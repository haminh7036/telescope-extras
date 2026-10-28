# Tone & Writing Style
- Write in a direct, technical, and documentation-first style.
- State facts, mechanisms, and behaviors clearly; avoid filler, marketing adjectives, or speculative claims.
- Do not use emojis, icons, or decorative symbols anywhere (headings, lists, body text).
- Use short sentences and bulleted lists.

# Issue Management
- Language: English only.
- Tone: Strictly neutral and constructive. Never use blaming, negative, or frustrated language regarding upstream maintainers or closed PRs; treat upstream decisions as legitimate maintainability trade-offs and focus entirely on the technical use case.
- Check existing issues before opening a new one to prevent duplicates.
- Structure: Context, Steps to Reproduce (or Proposed Behavior), Expected Result, Actual Result, Environment.
- Include minimal reproducible code or tests for bug reports.
- Always present the draft issue for user approval before publishing to remote.

# Documentation Updates
- Language: English only.
- Sync documentation (README.md, docblocks, API references) in the same change as code updates.
- Document public interfaces, config options, and breaking changes factually and concisely.
- Maintain README formatting:
  - Do not add status badges, decorative banners, or a Table of Contents unless explicitly requested.
  - Feature bullet format: `- **Feature name:** <mechanism and behavior>.`
  - Attribution: If a feature is based on an external contributor's PR/idea, credit them (e.g., `Originally proposed by @author in [repo#123](url)`); omit attribution if it is the maintainer's own PR.
  - Code blocks must specify language syntax (`php`, `bash`). For config changes, show commented-out defaults alongside replacements.
  - Keep sections mechanism-first (`How it works`) and include rollback steps (`Uninstall`) when modifying configurations.
