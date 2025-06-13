
## Generate a Git commit message using this format:

```
<gitmoji> <type>(<scope>): <summary>

<description>

<references>
```

### Rules
1. **Gitmoji** (choose exactly one):  
   ✨feat 🐛fix ♻️refactor 🔥remove 📝docs 🚀perf 🎨style ✅test 📦️build ⚡️ci 🚧wip 🔧chore ⬆️upgrade ⬇️downgrade 🔀merge 🗃️db 💄ui 🗑️deprecate

2. **Type & Scope**:  
   `(<scope>)` is optional. Use lowercase (e.g., `feat(api)`, `fix(login)`)

3. **Summary**:  
   - Max 50 characters  
   - Start lowercase, no period  
   - Imperative tense ("add" not "added")

4. **Description** (optional):  
   - Concise "what + why" (1-2 sentences)

5. **References** (optional):  
   - Issues (e.g., `Closes #123`)  
   - Breaking changes (prefix with `BREAKING CHANGE:`)

### Example
```
🐛 fix(auth): handle expired password tokens

Added token expiration checks in verifyPasswordReset() 
to prevent reuse of outdated tokens.

```
