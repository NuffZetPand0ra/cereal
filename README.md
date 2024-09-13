# Cereal Product Management

This project is a web application for managing cereal products. It allows users to view, edit, and manage cereal product details.

## Prerequisites

Before you begin, ensure you have met the following requirements:
- You have installed [Docker](https://www.docker.com/get-started)
- You have a web browser installed

## Installation

To set up and run this project locally, follow these steps:

1. **Clone the repository**:
	```sh
	git clone https://github.com/NuffZetPand0ra/cereal.git
	cd cereal-product-management
	```

2. **Build the Docker images**:
	```sh
	docker-compose build
	```

3. **Start the Docker containers**:
	```sh
	docker compose up --pull always -d --wait
	```

4. **Run database migration to set up schema**:
    ```sh
    docker exec cereal-php-1 php bin/console doctrine:migrations:migrate --no-interaction
    ```

5. **(Optional) Load in the data fixtures to populate the database with starting data**:
    ```sh
    docker exec cereal-php-1 php bin/console doctrine:fixtures:load --no-interaction
    ```

## Usage

Once the Docker containers are up and running, open your web browser and navigate to:

http://localhost/products

You should see the home page of the Cereal Product Management application.

## Contributing

Contributions are welcome! Please follow these steps to contribute:

1. Fork the repository
2. Create a new branch (`git checkout -b feature-branch`)
3. Make your changes
4. Commit your changes (`git commit -m 'Add some feature'`)
5. Push to the branch (`git push origin feature-branch`)
6. Create a Pull Request

## License

This project is licensed under the MIT License.