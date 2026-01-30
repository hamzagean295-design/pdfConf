# PDF Config Manager v0.0.1

A tool to dynamically configure and generate PDF documents in a Laravel application.

## Features

-   Configure existing PDF files by adding static text.
-   Insert dynamic data into PDFs using a simple tagging system.
-   Manage PDF templates for various document types like invoices.

## Tech Stack

-   **Laravel 12.x**: The core backend framework.
-   **FPDF/FPDI**: PHP packages for creating and modifying PDF documents.
-   **Alpine.js & Vanilla JS**: Used for frontend interactions. A feature allows users to get coordinates (x, y) automatically using the cursor, which helps in placing elements on the PDF. This feature is functional but requires further calibration.
-   **Livewire**: Used for a single component (`⚡template-name.blade.php`) to enable updating the template name with a double-click.
-   **Tailwind CSS**: For styling UI components like tables, buttons, etc.

## How does it work?

1.  **Upload Templates**: Upload your base PDF templates (e.g., an invoice layout).
2.  **Create a Document**: Create a new document (like an invoice) and select one of the uploaded templates.
3.  **Generate PDF**: The tool generates a new PDF, filling the selected template with the document data and any pre-configured static text.

## What problem does this tool solve?

This tool simplifies the process of modifying PDF templates in a Laravel project. Instead of manually coding changes to a PDF's structure every time requirements change, you can integrate this tool to manage PDF configurations dynamically through a user-friendly interface.

## Project Structure

Here is a simplified overview of the main project directories:

```
.
app
├── Http
│   └── Controllers
│       ├── Controller.php
│       ├── DocumentGeneratorController.php
│       └── FactureController.php
├── Models
│   ├── Document.php
│   └── Facture.php
├── Providers
│   └── AppServiceProvider.php
└── Services
    └── PdfGenerator
        ├── Contracts
        │   └── ElementRendererInterface.php
        ├── PdfGeneratorService.php
        └── Renderers
            ├── DynamicTagRenderer.php
            ├── ImageRenderer.php
            └── StaticTextRenderer.php
resources
├── css
│   └── app.css
├── js
│   ├── app.js
│   └── bootstrap.js
└── views
    ├── components
    │   ├── app-layout.blade.php
    │   └── ⚡template-name.blade.php
    ├── documents
    │   ├── create.blade.php
    │   └── index.blade.php
    ├── edit-simple.blade.php
    ├── facture
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   ├── index.blade.php
    │   └── show.blade.php
    ├── layouts
    │   └── app.blade.php
    └── welcome.blade.php
routes
├── console.php
└── web.php
storage/app/public
├── factures
└── templates

```

## Architecture

### Backend

The core logic resides in `App\Services\PdfGeneratorService.php`. This service utilizes the **Strategy** design pattern through the `ElementRendererInterface.php` interface.

This allows for different rendering strategies for various elements:
-   `StaticTextRenderer.php`
-   `DynamicTagRenderer.php`
-   `ImageRenderer.php`

### Frontend

The frontend is built with simple and functional components to ensure ease of use.

---

**Author**: AIT ADDI Hamza
