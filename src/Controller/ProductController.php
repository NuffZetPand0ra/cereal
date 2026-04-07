<?php

namespace App\Controller;

use App\Entity\Manufacturer;
use App\Entity\Product;
use App\Entity\ProductImage;
use App\Entity\ProductType;
use App\Service\ProductFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{
    public function index(Request $r, EntityManagerInterface $em, ProductFilterService $productFilterService): Response
    {
        $filters = $productFilterService->parseFiltersFromArray($this->buildIndexFilterQuery($r));
        $products = $em->getRepository(Product::class)->findWithFilters($filters);
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'products' => $products,
            'filters' => $filters,
        ]);
    }

    private function buildIndexFilterQuery(Request $request): array
    {
        $query = $request->query;
        $normalizedFilters = [];

        $nameContains = trim((string) $query->get('name_contains', ''));
        if ($nameContains !== '') {
            $normalizedFilters['name'] = '%'.$nameContains.'%';
        }

        $rangeFields = ['calories', 'protein', 'sugars', 'fiber'];
        foreach ($rangeFields as $field) {
            $min = trim((string) $query->get($field.'_min', ''));
            $max = trim((string) $query->get($field.'_max', ''));

            if ($min !== '' && $max !== '') {
                $normalizedFilters[$field] = $min.'..'.$max;
            } elseif ($min !== '') {
                $normalizedFilters[$field] = '>='.$min;
            } elseif ($max !== '') {
                $normalizedFilters[$field] = '<='.$max;
            }
        }

        $sortBy = trim((string) $query->get('sort_by', 'name'));
        $sortDirection = strtolower(trim((string) $query->get('sort_dir', 'asc')));
        $allowedSortFields = ['name', 'calories', 'protein', 'sugars', 'fiber'];
        if (!in_array($sortBy, $allowedSortFields, true)) {
            $sortBy = 'name';
        }
        $normalizedFilters['order'] = ($sortDirection === 'desc' ? '>' : '<').$sortBy;

        return $normalizedFilters;
    }
    
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $product = $em->getRepository(Product::class)->find($id);
        if(!$product) {
            throw $this->createNotFoundException('Product with id '.$id.' not found');
        }
        $image = $em->getRepository(ProductImage::class)->findOneBy(['product' => $product]);
        $image_base64 = null;
        if ($image) {
            $image_base64 = base64_encode(stream_get_contents($image->getImageData()));
        }
        // var_dump(base64_encode(stream_get_contents($image->getImageData())));exit;
        return $this->render('product/single.html.twig', [
            'controller_name' => 'ProductController',
            'product' => $product,
            'id' => $id,
            'image' => $image,
            'image_base64' => $image_base64,
        ]);
    }

    public function edit(int $id, EntityManagerInterface $em): Response
    {
        $product = $em->getRepository(Product::class)->find($id);
        if(!$product) {
            throw $this->createNotFoundException('Product with id '.$id.' not found');
        }
        $manufacturers = $em->getRepository(Manufacturer::class)->findAll();
        $types = $em->getRepository(ProductType::class)->findAll();
        return $this->render('product/edit.html.twig', [
            'controller_name' => 'ProductController',
            'product' => $product,
            'mfrs' => $manufacturers,
            'types' => $types,
        ]);
    }

    public function create(EntityManagerInterface $em): Response
    {
        $manufacturers = $em->getRepository(Manufacturer::class)->findAll();
        $types = $em->getRepository(ProductType::class)->findAll();
        return $this->render('product/edit.html.twig', [
            'controller_name' => 'ProductController',
            'mfrs' => $manufacturers,
            'types' => $types,
        ]);
    }

    public function save(Request $r, EntityManagerInterface $em, ValidatorInterface $validator) : Response
    {
        $numericFieldRules = [
            'calories' => false,
            'protein' => false,
            'fat' => false,
            'sodium' => false,
            'fiber' => true,
            'carbo' => true,
            'sugars' => false,
            'potass' => false,
            'vitamins' => false,
            'shelf' => false,
            'weight' => true,
            'cups' => true,
        ];

        if($r->get('id')) {
            $product = $em->getRepository(Product::class)->find($r->get('id'));
        } else {
            $product = new Product();
        }

        if(!$product) {
            $this->addFlash('error', 'Product not found');
            return $this->redirectToRoute('product_index');
        }

        $name = trim((string) $r->get('name'));
        $mfr = $em->getRepository(Manufacturer::class)->find($r->get('mfr'));
        $type = $em->getRepository(ProductType::class)->find($r->get('type'));
        $uploadedImage = $r->files->get('image');
        $errors = [];

        if (!$mfr) {
            $errors[] = 'Please choose a valid manufacturer.';
        }
        if (!$type) {
            $errors[] = 'Please choose a valid type.';
        }

        $numericValues = $this->parseNumericInputs($r, $numericFieldRules, $errors);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error);
            }

            return $this->redirectToProductForm($product);
        }

        $product
            ->setName($name)
            ->setMfr($mfr)
            ->setType($type)
            ->setCalories($numericValues['calories'])
            ->setProtein($numericValues['protein'])
            ->setFat($numericValues['fat'])
            ->setSodium($numericValues['sodium'])
            ->setFiber($numericValues['fiber'])
            ->setCarbo($numericValues['carbo'])
            ->setSugars($numericValues['sugars'])
            ->setPotass($numericValues['potass'])
            ->setVitamins($numericValues['vitamins'])
            ->setShelf($numericValues['shelf'])
            ->setWeight($numericValues['weight'])
            ->setCups($numericValues['cups']);

        $productViolations = $validator->validate($product);
        foreach ($productViolations as $violation) {
            $this->addFlash('error', $violation->getMessage());
        }

        if (count($productViolations) > 0) {
            return $this->redirectToProductForm($product);
        }

        if ($uploadedImage instanceof UploadedFile) {
            $imageViolations = $validator->validate($uploadedImage, [
                new Assert\Image(
                    maxSize: '5M',
                    mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                    maxSizeMessage: 'Image is too large. Maximum allowed size is 5MB.',
                    mimeTypesMessage: 'Image must be JPG, PNG, WEBP, or GIF.'
                ),
            ]);

            foreach ($imageViolations as $violation) {
                $this->addFlash('error', $violation->getMessage());
            }

            if (count($imageViolations) > 0) {
                return $this->redirectToProductForm($product);
            }
        }

        try{
            $em->persist($product);

            if ($uploadedImage instanceof UploadedFile && $uploadedImage->isValid()) {
                $imageInfo = getimagesize($uploadedImage->getPathname());
                if ($imageInfo === false) {
                    throw new \RuntimeException('Uploaded file is not a valid image');
                }

                $image = $em->getRepository(ProductImage::class)->findOneBy(['product' => $product]);
                if (!$image) {
                    $image = new ProductImage();
                    $image->setProduct($product);
                }

                $image->setMimeType($imageInfo['mime'] ?? $uploadedImage->getMimeType() ?? 'application/octet-stream');
                $image->setWidth($imageInfo[0]);
                $image->setHeight($imageInfo[1]);
                $image->setImageData(file_get_contents($uploadedImage->getPathname()));
                $em->persist($image);
            }

            $em->flush();
            $this->addFlash('success', 'Product saved');
            return $this->redirectToRoute('single_product_show', ['id' => $product->getId()]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error saving product: '.$e->getMessage());
            return $this->redirectToProductForm($product);
        }
    }

    /**
     * @param array<string, bool> $numericFieldRules
     * @param array<int, string> $errors
     * @return array<string, int|float|null>
     */
    private function parseNumericInputs(Request $request, array $numericFieldRules, array &$errors): array
    {
        $parsedValues = [];

        foreach ($numericFieldRules as $field => $isFloat) {
            $raw = $request->get($field);
            if ($raw === null || $raw === '') {
                $parsedValues[$field] = $field === 'potass' ? null : 0;
                if ($field !== 'potass') {
                    $errors[] = ucfirst($field).' is required.';
                }
                continue;
            }

            $normalized = str_replace(',', '.', trim((string) $raw));
            if (!is_numeric($normalized)) {
                $errors[] = ucfirst($field).' must be numeric.';
                $parsedValues[$field] = $field === 'potass' ? null : 0;
                continue;
            }

            $value = $isFloat ? (float) $normalized : (int) $normalized;
            if ($value < 0) {
                $errors[] = ucfirst($field).' must be 0 or higher.';
            }

            $parsedValues[$field] = $value;
        }

        return $parsedValues;
    }

    private function redirectToProductForm(Product $product): Response
    {
        if ($product->getId()) {
            return $this->redirectToRoute('single_product_edit', ['id' => $product->getId()]);
        }

        return $this->redirectToRoute('single_product_create');
    }

    public function delete(int $id, EntityManagerInterface $em) : Response
    {
        $product = $em->getRepository(Product::class)->find($id);
        $product_image = $em->getRepository(ProductImage::class)->findOneBy(['product' => $product]);
        if(!$product) {
            $this->addFlash('error', 'Product with id '.$id.' not found');
            return $this->redirectToRoute('product_index');
        }try{
            $em->remove($product);
            if($product_image) {
                $em->remove($product_image);
            }
            $em->flush();
            $this->addFlash('success', "Deleted product ".$product->getName());
            return $this->redirectToRoute('product_index');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error deleting product '.$product->getName().': '.$e->getMessage());
            return $this->redirectToRoute('product_index');
        }
    }
}
