---
name: devcontainer-commands
description: >-
  Use this skill whenever commands need to be executed within the VS Code Dev Container, Docker environment, or WordPress runtime (such as WP-CLI, bash scripts, database migrations, package managers, or container startup). Instructs Antigravity to prompt the user with the exact command to run and ask them to submit the terminal results.
---

# Dev Container Command Delegation

## Overview

In this project, development and runtime operations are containerized using **VS Code Dev Containers** and Docker. Rather than executing container commands or Docker lifecycle operations directly from the host assistant, Antigravity delegates them to the user running inside the Dev Container terminal (or WSL).

---

## When to Use This Skill

Activate and follow this skill whenever:
1. Running **WP-CLI** commands (e.g., `wp core install`, `wp theme activate`, `wp rewrite flush`).
2. Running **Docker Compose** or container lifecycle commands (e.g., `docker compose up -d`, `docker compose down`).
3. Running **repository scripts** that interact with containers (e.g., `bash scripts/init-site.sh`, `bash scripts/backup-db.sh`, `bash scripts/export-for-hosting.sh`).
4. Running **Composer**, **npm**, or PHP tooling intended to run within the `/workspace` container environment.
5. Verifying database connectivity or inspecting internal service logs.

---

## Delegation Protocol

When an action requires running commands:

### Step 1: Formulate the Command
- Prepare the exact, copy-pasteable command.
- Ensure all parameters, environment flags, and paths are accurate.
- If multiple commands are needed, present them sequentially or as a logical script block.

### Step 2: Present the Command Clearly to the User
Provide a dedicated section in your response containing:
- **Terminal Location**: Where the user should run it (e.g., *"In your VS Code Dev Container terminal"* or *"In your WSL terminal"*).
- **The Command**: In a fenced code block with appropriate language highlighting.
- **Purpose**: A 1-2 sentence explanation of what the command does.

#### Standard Prompt Format:
```markdown
Please run the following command in your **VS Code Dev Container terminal**:

```bash
<exact command here>
```

**What this does**: <Brief explanation>

Once completed, please copy and paste the terminal output here so I can verify the results and proceed to the next step.
```

### Step 3: Stop and Await User Submission
- Do **not** attempt to run alternative commands or speculate on the outcome.
- Allow the user to execute the command and submit their output.

### Step 4: Evaluate the Submitted Results
- When the user pastes the output:
  - Check for success messages, warnings, or exit codes.
  - If successful, proceed to the next phase of the implementation plan.
  - If errors occurred, analyze the output, diagnose the cause, and provide a targeted fix or follow-up command.
