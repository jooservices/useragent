# Examples

This directory contains executable PHP scripts demonstrating every feature of the UserAgent library.

## How to Run

```bash
php docs/examples/01-basic-generation.php
```

## Contents

- **[01-basic-generation.php](01-basic-generation.php)**:  
  Simple usage, random generation, and deterministic seeding.

- **[02-profiles-and-shortcuts.php](02-profiles-and-shortcuts.php)**:  
  Using the `Profiles` helper for common devices (iPhone, Android, Desktop Chrome).

- **[03-specific-browser-specs.php](03-specific-browser-specs.php)**:  
  Generating UA strings for specific browsers (Chrome, Firefox, Safari, Edge).

- **[04-complex-constraints.php](04-complex-constraints.php)**:  
  Combining multiple constraints: custom OS, device type, versions, locales, and architectures.

- **[05-strategies.php](05-strategies.php)**:  
  Using different selection algorithms (Weighted, Uniform, Round Robin, Avoid Recent).

- **[06-error-handling.php](06-error-handling.php)**:  
  How to handle exceptions when constraints are impossible (e.g., Safari on Windows).

- **[07-deterministic-history.php](07-deterministic-history.php)**:  
  Using seeds for reproducible results and configuring RandomSpec (retry budget, history window).
