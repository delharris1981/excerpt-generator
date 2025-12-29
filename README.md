# Luhn Excerpt Generator (v1.1.2)

A high-performance WordPress plugin that leverages the **Luhn Heuristic Algorithm** to generate intelligent, context-aware excerpts.

## 🚀 Alpha Testing Guide

### Installation
1. Zip the contents of this folder (or download the latest release from GitHub).
2. Upload to your WordPress site via **Plugins > Add New > Upload Plugin**.
3. Activate **Luhn Excerpt Generator**.
4. **Auto-updates**: The plugin includes a GitHub Update Manager to notify you of new alpha releases directly in your dashboard.

### Core Features to Test
- **Settings**: Visit `Settings > Luhn Excerpts`. Try changing the "Sentence Count" and toggling "Auto-generate".
- **Multi-language Support**: Select between **English** and **Russian**. The algorithm uses Unicode-safe tokenization and localized stop words for accurate Cyrillic summarization.
- **Auto-Generation**: Create a new post, paste a long article, and save. Check if the Excerpt field is automatically populated.
- **Manual Generation**: Open an existing post. In the Excerpt panel (Sidebar in Gutenberg, beneath content in Classic), click the **✨ Generate Luhn Excerpt** button.
- **Algorithm Quality**: Test with various types of content (Technical, Narrative, News) and verify the 10% highest frequency word clusters are being captured.

## 🛠 Technical Details
- **Algorithm**: Luhn's Keyword Frequency & Cluster Scoring ($S = k^2 / w$).
- **Languages**: English and Russian support with pre-defined stop word lists.
- **Requirements**: PHP 8.2+
- **Architecture**: PSR-4 Autoloading, OOP-based Hooks, and strict typing.
- **Updates**: Integrated with GitHub Releases API for seamless update notifications and auto-activation after updates.

## 🐛 Reporting Bugs
Since this is an **Alpha** release, please report any issues regarding:
1. Sentence splitting errors (e.g., abbreviations like "Dr." causing false splits).
2. Logic failures on extremely short or extremely long posts.
3. UI layout shifts in the Gutenberg sidebar.
4. Translation or tokenization issues in Russian content.

---
*Created by Antigravity AI for Advanced WordPress Workflows.*
