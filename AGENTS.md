# GamesNET Agent Development Guide

This document serves as the primary reference for AI agents and developers working on the GamesNET project. It outlines the architecture, workflows, and strict rules required to maintain the stateless nature of the game engine.

## 1. Project Purpose & Architecture

GamesNET is a **stateless high-performance casino game server** built on PHP (Workerman) and TypeScript (Bun). Unlike traditional architectures, this system removes direct database dependencies from the game logic, relying instead on injected state and model hydration.

### Core Components

- **Runtime**: Bun (TypeScript test runner & utilities) + PHP (Game Server).
- **Server**: `workerman/workerman` running on `text://127.0.0.1:8787`.
- **Communication**: JSON payloads over TCP.
- **State Management**: Models are hydrated from JSON inputs, manipulated in memory, and state changes are returned to the caller.

## 2. Critical Rules

1.  **⛔ NEVER Modify `.originals`**: Files in the `.originals` directory are reference archives of the legacy Laravel/Database implementations. They exist solely for comparison and reverse-engineering logic.
2.  **⚡ Use Bun**: All TypeScript execution and package management must use `bun`.
3.  **🚫 Stateless Logic Only**: Game classes (`Server.php`, `SlotSettings.php`) must **never** attempt to connect to a database (MySQL, Redis, etc.) directly. All data must come from the input payload.

## 3. Development Workflow

### A. Starting the Game Server

The PHP Workerman server must be running to process game requests.

```bash
# In Terminal 1
php start.php start
```
