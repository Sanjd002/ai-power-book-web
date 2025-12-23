# AI Book Summary Generator

A web application that enables users to discover books and generate intelligent summaries using Google Gemini AI. This platform integrates the OpenLibrary API for book metadata and provides a secure user authentication system to manage personal libraries.

## Features

* User Authentication: Secure account creation and login functionality.
* Book Search: Browse a vast catalog of titles via the OpenLibrary API.
* AI-Powered Summaries: Generate concise book overviews using Google Gemini.
* Personal Collection: Save books and their generated summaries to a user dashboard.
* Database Management: Persistent storage for users, books, and summaries.

## Tech Stack

* Framework: Laravel
* Database: MySQL
* AI Integration: Google Gemini API
* Data Source: OpenLibrary API
* Frontend: Tailwind CSS / Blade Templates

## Installation

1. Clone the repository:
  https://github.com/Sanjd002/ai-power-book-web.git
   cd project-name

2. Install PHP dependencies:
   composer install

3. Install and compile frontend assets:
   npm install && npm run build

4. Setup environment variables:
   cp .env.example .env

5. Configure your .env file:
   * Add MySQL database credentials.
   * Add your Gemini API Key: GEMINI_API_KEY=your_api_key_here

6. Generate application key:
   php artisan key:generate

7. Run database migrations:
   php artisan migrate

8. Start the development server:
   php artisan serve

## Usage

1. Register for a new account.
2. Use the search bar to find a book by title or author.
3. Click on a book to view its details.
4. Click the "Generate Summary" button to trigger the Gemini AI request.
5. View and save the summary to your profile.

## License

This project is open-sourced software licensed under the MIT license.
