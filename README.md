# Luhn Excerpt Generator (v1.0.1)

A high-performance WordPress plugin that leverages the **Luhn Heuristic Algorithm** to generate intelligent, context-aware excerpts.

## 🚀 Alpha Testing Guide

### Installation
1. Zip the contents of this folder (or download the release from GitHub).
2. Upload to your WordPress site via **Plugins > Add New > Upload Plugin**.
3. Activate **Luhn Excerpt Generator**.

### Core Features to Test
- **Settings**: Visit `Settings > Luhn Excerpts`. Try changing the "Sentence Count" and toggling "Auto-generate".
- **Auto-Generation**: Create a new post, paste a long article, and save. Check if the Excerpt field is automatically populated.
- **Manual Generation**: Open an existing post. In the Excerpt panel (Sidebar in Gutenberg, beneath content in Classic), click the **✨ Generate Luhn Excerpt** button.
- **Algorithm Quality**: Test with various types of content (Technical, Narrative, News) and verify the 10% highest frequency word clusters are being captured.

## 🛠 Technical Details
- **Algorithm**: Luhn's Keyword Frequency & Cluster Scoring ($S = k^2 / w$).
- **Requirements**: PHP 8.2+
- **Architecture**: PSR-4 Autoloading, OOP-based Hooks & AJAX handlers.

## 🐛 Reporting Bugs
Since this is an **Alpha** release, please report any issues regarding:
1. Sentence splitting errors (e.g., abbreviations like "Dr." causing false splits).
2. Logic failures on extremely short or extremely long posts.
3. UI layout shifts in the Gutenberg sidebar.

---
*Created by Antigravity AI for Advanced WordPress Workflows.*
