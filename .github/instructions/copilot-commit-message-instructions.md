Generate a conventional commit message with an emoji, using this format:
<emoji> <type>(<scope>): <subject>
Include:
Type (feat, fix, docs, etc.) with correct emoji: ✨ 🐛 📚 💎 📦 🚀 🚨 🛠 ⚙️ ♻️ 🗑
Scope: affected file/module (e.g., login-form, api/user.js)
Subject: imperative, ≤50 chars
Body (optional): describe what changed (e.g., added, removed, refactored), list files/lines touched
Mention breaking changes or issues (e.g., fix #123) if applicable
Example:
📦 refactor(auth): simplify token handler logic
Refactored auth/token.js, removed legacyTokenCheck.js
