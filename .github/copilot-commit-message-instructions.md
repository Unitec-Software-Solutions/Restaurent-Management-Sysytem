# Conventional Commit Guidelines

Follow the [Conventional Commits 1.0.0](https://www.conventionalcommits.org/en/v1.0.0/) specification.

## Format

```
[<emoji>]<type>[optional scope]: <description>

[body]

[footer]
```

---

## Types

| Type       | Description               | Emoji         | Example                              |
|------------|--------------------------|---------------|--------------------------------------|
| feat       | New feature               | ✨ 🎉         | feat(auth): add SSO login            |
| fix        | Bug fix                   | 🐛            | fix(search): handle empty queries    |
| docs       | Documentation             | 📜 📝         | docs: update API examples            |
| refactor   | Code restructuring        | 🔨 ♻️         | refactor: optimize data fetching     |
| perf       | Performance improvement   | ⚡️ ⏱️        | perf: reduce bundle size             |
| test       | Tests                     | ✅ 🧪         | test: add checkout flow tests        |
| chore      | Maintenance tasks         | 🧹 🔧         | chore: update dependencies           |
| style      | Code formatting/UI        | 💄 🎨         | style: update button colors          |
| build      | Build system              | 📦️           | build: migrate to Vite               |
| ci         | CI/CD pipelines           | ⚙️ 💚         | ci: add e2e test stage               |

### Extended Types

| Emoji      | Use Case              | Example                              |
|------------|-----------------------|--------------------------------------|
| 🗑️ 🔥      | Remove code           | remove: legacy API endpoints         |
| 🛡️ 🔒      | Security              | fix(security): patch CVE-2023        |
| 🌐 💬      | i18n/l10n             | feat(i18n): add German locale        |
| 🏗️         | Infrastructure        | infra: configure CDN caching         |
| ♿         | Accessibility         | a11y: add screen reader labels       |
| 📊 🗃️      | Data/database         | db: add user_migration table         |
| 🚧         | WIP/Experimental      | wip: new payment flow                |

---

## Commit Structure

- **Type** (required): See tables above.
- **Scope** (recommended): Short noun for code area, e.g. `fix(api):`
- **Description** (required): Imperative, lowercase, ≤50 chars, no period.
- **Body** (optional): Explain *why*, wrap at 72 chars, blank line before.
- **Footer** (optional): Issues, breaking changes, tickets.

### Example

```bash
✨ feat(chat): add file sharing

Migrate legacy service to improve:
- Error handling
- Type safety

BREAKING CHANGE: Payment service API v2
Fixes #112
```

---

## Tips

- Place emoji before type (`✨ feat: ...`)
- Use standard scopes (`api`, `ui`, `db`)
- Add `BREAKING CHANGE:` in footer for breaking changes
- Reference issues/tickets in the footer
---

## Pro Tips

- **Emoji First**: Place emoji before type (`✨ feat: ...`)
- **Breaking Changes**: Always add a `BREAKING CHANGE:` footer
- **Scope Consistency**: Use standard scopes (e.g., `api`, `ui`, `db`)
- **Body Separator**: Leave a blank line between description and body
- **Reference Tickets**: Add ticket references in the footer

---

## Good Examples

```bash
✨ feat(chat): add file sharing

📝 docs: update contribution guide

🐛 fix(video): handle 4K streaming error

♻️ refactor(checkout): split payment module

BREAKING CHANGE: Payment service API v2
Fixes #112
```
