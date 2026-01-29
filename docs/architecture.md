# Architecture

This document describes the internal design of the UserAgent library (Generative Mode).

## Overview

The library generates User-Agent strings by combining **Templates** with randomized **Components** (versions, models, locales) according to a **GenerationSpec**.

## Core Components

### 1. Service Layer (`src/Service`)
- **`UserAgentService`**: The main facade. It orchestrates the entire process:
  1. Validates the `GenerationSpec`.
  2. Selects a `BrowserTemplate` via a `SelectionStrategy`.
  3. Uses `UserAgentRenderer` to resolve the template into a string.
  4. Stores the result in `LruHistory`.
- **`Profiles`**: A simplified API wrapper around `UserAgentService` for common use cases.

### 2. Templates (`src/Templates`)
- **`BrowserTemplate`**: Abstract base class defining browser capabilities.
- **Implementations**: `ChromeTemplate`, `FirefoxTemplate`, `SafariTemplate`, `EdgeTemplate`.
- Each template knows:
  - Its UA string format suitable for `sprintf`.
  - Supported platforms (OS, Device Type).
  - Version constraints.

### 3. Catalogs (`src/Templates/Catalogs`)
- Static repositories of realistic data used to fill template placeholders:
  - `ModelCatalog`: Device models (e.g., "Pixel 7", "iPhone 15").
  - `LocaleCatalog`: Language codes (e.g., "en-US", "de-DE").
  - `ArchCatalog`: CPU architectures (e.g., "x86_64", "arm64").

### 4. Logic & Pickers (`src/Pickers`)
- Specialized logic to select valid components based on constraints:
  - `VersionPicker`: Selects a version within min/max ranges.
  - `ModelPicker`: Selects a device model appropriate for the OS.
  - `LocalePicker`: Selects a locale.
  - `ArchPicker`: Selects an architecture appropriate for the device/OS.

### 5. Filtering (`src/Filters`)
- Classes that implement `FilterInterface` (conceptually) to verify if a template matches the `GenerationSpec`.
- Examples: `BrowserFilter`, `OsFilter`, `DeviceFilter`.

### 6. Strategies (`src/Strategies`)
- Algorithms to select which Browser Template to use:
  - **`WeightedStrategy`**: Uses defined market share data (most realistic).
  - **`UniformStrategy`**: Picks any matching browser with equal probability.
  - **`RoundRobinStrategy`**: Cycles through browsers sequentially.
  - **`AvoidRecentStrategy`**: Tries to avoid recently generated browsers.

### 7. Foundation (`src/Spec` & `src/Domain`)
- **`GenerationSpec`**: Immutable value object defining user constraints.
- **`RandomSpec`**: Configuration for randomness (seeds, weighting).
- **`Enums`**: Strictly typed definitions for `BrowserFamily`, `OperatingSystem`, `DeviceType`, etc.

## Data Flow

```
User Request -> UserAgentService -> GenerationSpec
       |
       v
  Strategies (Select BrowserTemplate)
       |
       v
  Filters (Verify Template matches Spec)
       |
       v
  Pickers (Select Version, Model, Locale for Template)
       |
       v
  UserAgentRenderer (Combine Template + Components)
       |
       v
  User-Agent String
```
