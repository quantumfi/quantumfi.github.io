# QuantumFi Website

A static, light-mode landing page for QuantumFi, designed for GitHub Pages.

## Files

- `index.html` - main landing page
- `styles.css` - responsive visual design
- `script.js` - mobile navigation, current year, reveal animations
- `assets/quantumfi-preview.svg` - social sharing preview placeholder
- `assets/finance-observatory.svg` - Finance Observatory logo
- `assets/reservoir-simulator.svg` - Reservoir Simulator logo
- `assets/quantumfi-publisher.svg` - QuantumFi Publisher logo

## Preview Locally

Open `index.html` directly in your browser, or run a small local server:

```powershell
python -m http.server 8000
```

Then visit:

```text
http://localhost:8000
```

## Publish on GitHub Pages

1. Create a GitHub repository.
2. Upload `index.html`, `styles.css`, `script.js`, and `README.md`.
3. In the repository, go to `Settings` -> `Pages`.
4. Under `Build and deployment`, choose `Deploy from a branch`.
5. Select the `main` branch and `/root` folder.
6. Save. GitHub will provide the public website URL.

## Before Publishing

- Replace `hello@quantumfi.example` with the company email.
- Replace the Formspree form URL in `index.html` with your real form endpoint.
- Replace the Open Graph preview image path if you add a real preview image.
