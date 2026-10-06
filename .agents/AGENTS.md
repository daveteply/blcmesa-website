# Project Rules for blcmesa.org

## Dev Container Command Execution Protocol

- **Dev Container Command Delegation**: When any commands need to be executed within the VS Code Dev Container, Docker environment, or containerized WordPress runtime (such as `docker compose`, `wp`, `bash scripts/...`, `npm`, or `composer`), do **not** run them autonomously from the host agent.
- **Prompt User for Execution**: Present the exact command in a clean, copyable code block specifying the target terminal (VS Code Dev Container or WSL), explain what it does, and ask the user to run it and paste the output back into the conversation.
- Refer to the local skill [`.agents/skills/devcontainer-commands/SKILL.md`](file://./.agents/skills/devcontainer-commands/SKILL.md) for full details on this workflow.
