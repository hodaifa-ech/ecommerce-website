# E-commerce Website

Welcome to our e-commerce website project! This project aims to create a robust online shopping platform with features such as product browsing, ordering, payment processing, and admin management.

## Technologies Used

- **Symfony**: Symfony is a PHP framework used for building web applications. It provides a solid foundation for developing scalable and maintainable projects.
- **Bootstrap**: Bootstrap is a front-end framework for developing responsive and mobile-first websites. It helps ensure a consistent and visually appealing user interface.
- **JavaScript (JS)**: JavaScript adds interactivity and dynamic functionality to the website, enhancing user experience.
- **PayPal Integration**: PayPal is integrated into the website for secure and convenient payment processing.
- **WhatsApp Verification**: WhatsApp is used for user verification purposes, enhancing security.
- **Admin Panel**: An intuitive admin panel is provided for managing products, orders, and other aspects of the website.

## Getting Started

To get started with the project, follow these steps:

1. Clone the repository: `git clone <repository-url>`
2. Install dependencies: `composer install`
3. Set up your environment variables, including PayPal API credentials and WhatsApp integration details.
4. Set up your database configuration in `.env` file.
5. Run migrations to set up the database schema: `php bin/console doctrine:migrations:migrate`
6. Start the Symfony server: `symfony serve`

## Features

- Browse products by category or search for specific items.
- Add products to the cart and proceed to checkout.
- Secure payment processing through PayPal.
- User verification via WhatsApp.
- Admin panel for managing products, orders, and users.

## Contributing

Contributions are welcome! If you'd like to contribute to the project, please follow these guidelines:
- Fork the repository.
- Create your feature branch: `git checkout -b feature-name`
- Commit your changes: `git commit -m 'Add some feature'`
- Push to the branch: `git push origin feature-name`
- Submit a pull request.

## License

This project is licensed under the [MIT License](LICENSE).
