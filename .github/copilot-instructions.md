# Project Instructions
## Stack Overview
- **Backend:** Laravel (PHP)
- **Database:** PostgreSQL
- **Frontend/UI:** Tailwind CSS

# admins
- **super admin:** doesn't have an org id, doesn't have a branch id  
    - Super admin model: `'is_super_admin' => 'boolean'`
    - Scope example:
        ```php
        public function scopeSuperAdmin($query)
        {
                return $query->where('is_super_admin', true);
        }
        ```
- **organization admin:** has an org id, doesn't have a branch id  
- **branch admin:** has an org id, has a branch id

