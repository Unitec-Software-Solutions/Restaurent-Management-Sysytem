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
