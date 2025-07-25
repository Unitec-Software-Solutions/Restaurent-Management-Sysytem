# Project Instructions

## Stack Overview

- **Backend:** Laravel (PHP)
- **Database:** PostgreSQL
- **Frontend/UI:** Tailwind CSS

## Admins

- **Super admin:**  
    - Doesn't have an org id, doesn't have a branch id  
    - Super admin model: `'is_super_admin' => 'boolean'`
    - Scope example:
        ```php
        public function scopeSuperAdmin($query)
        {
                return $query->where('is_super_admin', true);
        }
        ```
    - Skip org verification if super admin, else continue with filtering and verifications as usual

- **Organization admin:**  
    - Has an org id, doesn't have a branch id

- **Branch admin:**  
    - Has an org id, has a branch id


## GRN Reports

- **Models Used:** GRN Master and GRN Item
- **Report Data:** Passed as an array to the print view
- **Export Options:** PDF download or browser print
- **Payments:** Use GRN Master value
- **Discounts:** Consider item-wise discounts when creating reports
- **Reference:** Existing GRN Controllers must be referred to when creating report controllers for each submodule

## GTN Reports

- **Models Used:** GTN Master and GTN Item
- **Report Data:** Passed as an array to the print view
- **Export Options:** PDF download or browser print
- **Values:** GTN Master value and item-wise discounts

## Stock Reports

- **Includes:** Current stock, stock movement (transaction record filtered by each item), and stock valuation reports
- **Models Used:** Stock Master and Stock Item
- **Report Data:** Passed as an array to the print view
- **Export Options:** PDF download or browser print
- **Details:** Stock levels are in the master; detailed info is in Stock Item and its transaction details

---

### Reports - Main Module

- **GRN** (Submodule)
    - GRN Master reports (components of GRN)
    - GRN Item reports (components of GRN)
- **GTN** (Submodule)
    - GTN Master reports (components of GTN)
    - GTN Item reports (components of GTN)
- **SRN** (Submodule)
    - SRN Master reports (components of SRN)
    - SRN Item reports (components of SRN)
- **Stock Reports** (Submodule)
    - Stock Master reports (components of Stock Reports)
    - Stock Item reports (components of Stock Reports)

## Print Reports

When printing reports, follow these guidelines:

- **Paper Size:** Always use A4.
- **Content Width:** All content must fit the width of the page.
- **Tables:** Tables must be responsive and fit the page width.
- **Margins:** Set margins to none.
- **Graphics:** Disable page graphics by default.
- **General:** Follow common printing guides for reports.

These instructions ensure printed reports are clear, professional, and consistent.



## Errors

Use `resources/views/errors/generic.blade.php` with customizable variables such as:

- `@extends('errors.generic')`

Examples of customizable variables:

- `errorTitle`: 'Page Not Found'
- `errorCode`: '404'
- `errorHeading`: 'Page Not Found'
- `errorMessage`: 'The page you are looking for could not be found.'
- `headerClass`: 'bg-gradient-warning'
- `errorIcon`: 'fas fa-map-marker-alt'
- `mainIcon`: 'fas fa-map-marker-alt'
- `iconBgClass`: 'bg-yellow-100'
- `iconColor`: 'text-yellow-500'
- `buttonClass`: 'bg-[#FF9800] hover:bg-[#e68a00]'

Use these for showing error pages or redirects if needed.



