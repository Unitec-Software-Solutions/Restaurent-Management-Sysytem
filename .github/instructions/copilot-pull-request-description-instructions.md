# Pull Request Description Guidelines

When submitting a pull request to the Restaurant Management System codebase, please follow these guidelines to ensure clarity and consistency:

## PR Title Format

- Use the format: `[TYPE]: Brief description of changes`
- Types: FEATURE, FIX, REFACTOR, STYLE, DOCS, TEST, PERF, CHORE
- Keep titles concise (≤72 characters), imperative mood (e.g., "Add menu endpoint")

## PR Description Structure

### Summary

Briefly summarize what the PR does in 1-2 sentences.

### What Changed

List the main changes in bullet points:
- New features, endpoints, or modules
- UI changes or new components
- Database schema changes
- Refactoring or performance improvements

### Why

Explain the motivation or problem this PR addresses.

### How

Describe the technical approach or solution, including:
- Key implementation details
- Any new patterns, libraries, or dependencies introduced

### Testing

Describe how the changes were tested:
- Unit/integration tests added or updated
- Manual testing steps (if applicable)

### Screenshots

Include before/after screenshots for UI changes, if relevant.

### Related Issues

Reference related issues or PRs using:
- Fixes #[issue-number]
- Closes #[issue-number]
- Related to #[issue-number]

### Additional Notes

Add any extra context, known issues, or follow-up work.

---

## Example

```
FEATURE: Add order management API

### Summary
Implements endpoints for creating, updating, and viewing restaurant orders.

### What Changed
- Added `/api/orders` endpoints (GET, POST, PUT)
- Created `OrderController` and service layer
- Updated database schema with `orders` table
- Added unit tests for order logic

### Why
Enables staff to manage customer orders through the system.

### How
- Used RESTful API design
- Integrated with existing authentication middleware
- Added input validation and error handling

### Testing
- Added unit tests for order service (100% coverage)
- Manually tested endpoints with Postman

### Screenshots
N/A

### Related Issues
Fixes #12

### Additional Notes
Requires DB migration before deployment.
```
List the key changes made in this pull request:

- Describe functional changes to game mechanics (if applicable)
- List UI component changes or additions
- Mention performance improvements
- Note any accessibility enhancements

### Testing

Explain how the changes were tested:

- Unit tests added/modified
- Component tests
- End-to-end tests
- Manual testing performed

### Screenshots/Videos

If the PR includes visual changes, note to include:

- Before/after screenshots for UI changes
- Video demonstration for animation or interaction changes

### Related Issues

Link to any related issues using the format:

- Fixes #[issue-number]
- Addresses #[issue-number]

### Accessibility Checklist

Confirm these accessibility items were addressed:

- [ ] Proper semantic HTML
- [ ] WCAG AAA color contrast compliance
- [ ] Keyboard navigation support
- [ ] Screen reader compatibility tested
- [ ] Appropriate ARIA attributes added

### Technical Debt

List any technical debt introduced or addressed by this PR.

## Example PR Description

```
## Summary
Added the difficulty selection component to allow users to choose between Easy, Medium, and Hard game modes.

## Changes
- Created new DifficultyPicker component with responsive design
- Implemented difficulty state management using Astro islands
- Added visual indicators for each difficulty level
- Updated game initialization to account for selected difficulty

## Testing
- Added unit tests for difficulty selection logic
- Added component tests for the DifficultyPicker
- Manually tested across mobile and desktop viewports

## Screenshots
[Include before/after screenshots here]

## Related Issues
- Fixes #42
- Addresses #38

## Accessibility Checklist
- [x] Proper semantic HTML
- [x] WCAG AAA color contrast compliance
- [x] Keyboard navigation support
- [x] Screen reader compatibility tested
- [x] Appropriate ARIA attributes added

## Technical Debt
None
```
