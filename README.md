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
```
app
├── Http
│   ├── Controllers
│   │   ├── CnssController.php
│   │   ├── Controller.php
│   │   ├── DocumentController.php
│   │   └── FactureController.php
│   └── Requests
│       ├── CnssRequest.php
│       ├── SaveDocumentConfigRequest.php
│       └── StoreDocumentRequest.php
├── Models
│   ├── Cnss.php
│   ├── Document.php
│   ├── Facture.php
│   └── User.php
├── Providers
│   └── AppServiceProvider.php
└── Services
    └── PdfGenerator
        ├── Contracts
        │   └── ElementRendererInterface.php
        ├── PdfGeneratorService.php
        └── Renderers
            ├── CheckboxRender.php
            ├── DynamicTagRenderer.php
            ├── ImageRenderer.php
            └── StaticTextRenderer.php
database
├── database.sqlite
├── factories
│   └── UserFactory.php
├── migrations
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_cache_table.php
│   ├── 0001_01_01_000002_create_jobs_table.php
│   ├── 2026_01_28_152413_create_documents_table.php
│   ├── 2026_01_29_114334_create_factures_table.php
│   ├── 2026_01_29_162822_add_template_id_to_factures_table.php
│   ├── 2026_01_30_144742_add_sexe_to_factures_table.php
│   └── 2026_01_31_111117_create_cnsses_table.php
└── seeders
    └── DatabaseSeeder.php
resources
├── css
│   └── app.css
├── js
│   ├── app.js
│   └── bootstrap.js
└── views
    ├── cnss
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   ├── index.blade.php
    │   └── show.blade.php
    ├── documents
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   └── index.blade.php
    ├── facture
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   ├── index.blade.php
    │   └── show.blade.php
    └── layouts
        └── app.blade.php
public
├── build
│   ├── assets
│   │   ├── app-4u5Jb2Nr.js
│   │   └── app-st8k4Iyk.css
│   └── manifest.json
├── favicon.ico
├── hot
├── index.php
├── js
│   ├── pdf.mjs
│   └── pdf.worker.mjs
├── robots.txt

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
