<?php

namespace App\Controller;

use App\Entity\Manufacturer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ManufacturerController extends AbstractController
{
    public function index(EntityManagerInterface $em): Response
    {
        $manufacturers = $em->getRepository(Manufacturer::class)->findBy([], ['name' => 'ASC']);

        return $this->render('manufacturer/index.html.twig', [
            'manufacturers' => $manufacturers,
        ]);
    }

    public function create(): Response
    {
        return $this->render('manufacturer/edit.html.twig', [
            'manufacturer' => null,
        ]);
    }

    public function edit(int $id, EntityManagerInterface $em): Response
    {
        $manufacturer = $em->getRepository(Manufacturer::class)->find($id);
        if (!$manufacturer) {
            throw $this->createNotFoundException('Manufacturer with id '.$id.' not found');
        }

        return $this->render('manufacturer/edit.html.twig', [
            'manufacturer' => $manufacturer,
        ]);
    }

    public function save(Request $request, EntityManagerInterface $em): Response
    {
        $id = $request->get('id');
        if ($id) {
            $manufacturer = $em->getRepository(Manufacturer::class)->find($id);
            if (!$manufacturer) {
                $this->addFlash('error', 'Manufacturer not found');
                return $this->redirectToRoute('manufacturer_index');
            }
        } else {
            $manufacturer = new Manufacturer();
        }

        $name = trim((string) $request->get('name'));
        $shorthand = strtoupper(trim((string) $request->get('shorthand')));

        if ($name === '' || mb_strlen($name) < 2) {
            $this->addFlash('error', 'Manufacturer name must have at least 2 characters.');
            return $id
                ? $this->redirectToRoute('single_manufacturer_edit', ['id' => $manufacturer->getId()])
                : $this->redirectToRoute('single_manufacturer_create');
        }

        if (mb_strlen($shorthand) < 1 || mb_strlen($shorthand) > 3) {
            $this->addFlash('error', 'Shorthand must be 1–3 characters.');
            return $id
                ? $this->redirectToRoute('single_manufacturer_edit', ['id' => $manufacturer->getId()])
                : $this->redirectToRoute('single_manufacturer_create');
        }

        $conflict = $em->getRepository(Manufacturer::class)->findOneBy(['shorthand' => $shorthand]);
        if ($conflict && $conflict->getId() !== $manufacturer->getId()) {
            $this->addFlash('error', 'That shorthand is already in use.');
            return $id
                ? $this->redirectToRoute('single_manufacturer_edit', ['id' => $manufacturer->getId()])
                : $this->redirectToRoute('single_manufacturer_create');
        }

        $manufacturer->setName($name);
        $manufacturer->setShorthand($shorthand);

        try {
            $em->persist($manufacturer);
            $em->flush();
            $this->addFlash('success', 'Manufacturer saved.');
            return $this->redirectToRoute('manufacturer_index');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error saving manufacturer: '.$e->getMessage());
            return $id
                ? $this->redirectToRoute('single_manufacturer_edit', ['id' => $manufacturer->getId()])
                : $this->redirectToRoute('single_manufacturer_create');
        }
    }

    public function checkShorthand(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $shorthand = strtoupper(trim((string) $request->query->get('shorthand', '')));
        $excludeId  = (int) $request->query->get('exclude_id', 0);

        if (mb_strlen($shorthand) < 1 || mb_strlen($shorthand) > 3) {
            return $this->json(['available' => false, 'error' => 'Shorthand must be 1–3 characters.']);
        }

        $conflict = $em->getRepository(Manufacturer::class)->findOneBy(['shorthand' => $shorthand]);

        if ($conflict && $conflict->getId() !== $excludeId) {
            return $this->json(['available' => false, 'usedBy' => $conflict->getName()]);
        }

        return $this->json(['available' => true]);
    }

    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $manufacturer = $em->getRepository(Manufacturer::class)->find($id);
        if (!$manufacturer) {
            $this->addFlash('error', 'Manufacturer not found.');
            return $this->redirectToRoute('manufacturer_index');
        }

        try {
            $em->remove($manufacturer);
            $em->flush();
            $this->addFlash('success', 'Manufacturer deleted.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Could not delete manufacturer: '.$e->getMessage());
        }

        return $this->redirectToRoute('manufacturer_index');
    }
}
