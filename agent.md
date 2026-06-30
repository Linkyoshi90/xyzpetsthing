# AGENTS.md — Harmontide Inc. AI Agent Policy
> Applies to: **OpenAI Codex**, **Anthropic Claude Code**, and any AI agent operating inside this repository.  
> Last revised: see `/docs/` workflow log for the active date.  
> Questions / exceptions → `#dev-ai-policy` on Slack or open a Policy RFC.

---

## 1. Scripting & Shell Safety

### 1.1 PowerShell is Prohibited
- **Do NOT generate, execute, or suggest PowerShell scripts** (`.ps1`, `pwsh`, `powershell.exe`, `Invoke-*` cmdlets, etc.).
- PowerShell payloads are flagged as trojans by Harmontide's endpoint-protection toolchain and will trigger an automatic security incident.
- Use **POSIX-compliant shell** (`bash`, `sh`, `zsh`) on Unix/macOS and **`cmd.exe` batch scripts** on Windows where a Windows-native script is strictly necessary.
- If a task genuinely requires a Windows-only operation, raise a ticket and wait for a human engineer to handle it.

### 1.2 No Privilege Escalation
- Operate strictly with the permissions of the **current user / process** that invoked the agent.
- Never call `sudo`, `su`, `runas`, `chmod 777`, `chown root`, `setuid/setgid`, UAC-bypass techniques, or any equivalent.
- Never modify `/etc/`, system registries, or OS-level configuration files.
- If a task requires elevated rights, **stop, document the blocker in the workflow log, and request human intervention**.

---

## 2. Project Boundary

- The agent's working tree is the **repository root** where this file lives.
- **Never read, write, copy, move, or delete files outside the project root**, including:
  - Parent directories (`../`)
  - Absolute paths outside the repo (`/home/`, `C:\Users\`, `/etc/`, `/tmp/` unless explicitly declared as an artifact output dir in `pyproject.toml` / `package.json`)
  - Network shares, mounted drives, or cloud-sync folders (Dropbox, OneDrive, iCloud) not explicitly listed in `.agentrc`
- Environment variables that reference external paths must be validated before use; reject if they point outside the project boundary.

---

## 3. Database & SQL Safety

SQL statements produced or executed by the agent **must not**:

| Prohibited | Examples |
|---|---|
| Escalate DB privileges | `GRANT`, `ALTER ROLE`, `CREATE USER`, `REVOKE` |
| Modify schema destructively | `DROP TABLE`, `DROP DATABASE`, `TRUNCATE` (unless inside an explicitly scoped migration) |
| Execute arbitrary dynamic SQL | `EXEC sp_executesql`, `EXECUTE IMMEDIATE` with unvalidated inputs |
| Access data outside the project's declared schemas | Cross-database queries, `information_schema` fishing beyond what's needed |
| Bypass row-level security | Disabling RLS, impersonating another DB user |
| Run bulk exports of PII | `SELECT * FROM users` without a `LIMIT` and proper access review |

Permitted patterns:
- Parameterised queries only (no string interpolation of user input).
- Migrations must be reversible (`up` + `down`) and reviewed by a human before running in staging or production.
- Read-only `SELECT` queries within the project's own schemas are fine for development.

---

## 4. Workflow Documentation

After **every task session**, the agent must create or append a Markdown log file:

```
/docs/yyyyMMdd_task.md          ← date-stamped per run (e.g. /docs/20260617_task.md)
```

### Required sections

```markdown
# Task Log — YYYY-MM-DD

## Objective
Short description of what was requested.

## Steps Taken
1. …
2. …

## Files Created / Modified
| File | Action | Notes |
|------|--------|-------|
| src/foo.ts | Created | Added X feature |

## Commands Executed
```bash
# list all shell commands that were actually run
```

## Decisions & Rationale
Any non-obvious choices, trade-offs, or deviations from the original request.

## Blockers / Human Action Required
List anything that could not be completed autonomously and why.

## Policy Checks Passed
- [ ] No PowerShell used
- [ ] No privilege escalation
- [ ] Stayed within project boundary
- [ ] SQL safety rules respected
- [ ] NSFW guidelines followed (if applicable)
```

The log is **append-only within a calendar day** — create a new file per day if multiple sessions occur.

---

## 5. Content Policy — NSFW & Sensitive Material

Harmontide operates in markets that may legitimately involve mature content (age-gated platforms, medical, legal, etc.). When generating such content the agent must:

1. **Confirm scope first** — NSFW output is only permitted when the current task is explicitly tagged `[NSFW-ALLOWED]` in the task description or a `harmontide.agent.yaml` config key `allow_nsfw: true` is present.
2. **Stay within company guidelines** — refer to `docs/policy/CONTENT_POLICY.md` for the full rubric. When in doubt, **err on the side of refusal** and log the decision.
3. **Never generate:**
   - Content involving minors in any sexual or suggestive context (absolute prohibition, no override).
   - Non-consensual scenarios presented approvingly.
   - Content designed to harass, dox, or harm a real, identifiable individual.
   - Material that violates applicable law in Switzerland (Harmontide's legal domicile) or the end-user's jurisdiction.
4. **Watermark / tag all NSFW output** with `<!-- harmontide:nsfw -->` in generated files so downstream tooling can gate it appropriately.

---

## 6. Secret & Credential Handling

- **Never hardcode** API keys, passwords, tokens, or certificates in source files, commit messages, or logs.
- Use environment variables or the project's secret manager (see `docs/SECRETS.md`).
- If a secret is accidentally exposed in a diff, immediately flag it in the workflow log under **"Blockers / Human Action Required"** and do not commit the file.
- Do not call external URLs that are not listed in `docs/ALLOWED_ENDPOINTS.md`; if a new endpoint is needed, request approval.

---

## 7. Dependency & Supply-Chain Safety

- Only add dependencies that are **already present in `package.json` / `pyproject.toml` / `go.mod`** or have been explicitly approved in the task description.
- Never install packages at runtime (no `pip install` / `npm install <pkg>` mid-script without prior approval).
- Pin new dependencies to an **exact version**; do not use `*`, `latest`, or open ranges.
- After any dependency change, regenerate the lockfile and include it in the same commit.

---

## 8. Testing & Code Quality

- All new code must include **unit tests**. Aim for the coverage threshold defined in `pyproject.toml` / `jest.config.*`.
- Do not modify or delete existing tests to make the suite pass — fix the underlying code instead.
- Run linters and formatters before considering a task done (`eslint`, `ruff`, `gofmt`, etc. as relevant).
- Do not commit with `--no-verify` or suppress CI checks.

---

## 9. Git Hygiene

- Commit messages follow **Conventional Commits**: `feat:`, `fix:`, `docs:`, `chore:`, etc.
- One logical change per commit; do not batch unrelated changes.
- Never force-push to `main`, `master`, `release/*`, or `hotfix/*`.
- Branch naming: `agent/<date>/<short-description>` (e.g. `agent/20260617/add-login-rate-limit`).

---

## 10. Agentic Loop Limits

To prevent runaway automation:

- **Max 50 file writes** per session without a human checkpoint.
- **Max 10 shell commands** per session without a human checkpoint.
- **Max 3 retries** on any failing step before stopping and logging a blocker.
- If the agent reaches a loop limit, it must write the workflow log, commit any safe partial work to a draft branch, and exit cleanly.

---

## 11. Audit & Compliance

- All agent actions are subject to review by the Harmontide Security & Compliance team.
- The workflow log (`/docs/yyyyMMdd_task.md`) is the primary audit trail — it must be accurate and complete.
- Falsifying or omitting entries in the log is a policy violation equivalent to falsifying human-authored records.
- Agents may be asked to produce a **diff summary** for any PR they author; always be prepared to explain every change.

---

## Quick-Reference Checklist (embed in every PR description)

```
### Agent Policy Checklist
- [ ] No PowerShell scripts generated or executed
- [ ] No privilege escalation attempted
- [ ] All file I/O stayed within project root
- [ ] SQL follows safety rules (no DDL drops, no privilege grants, parameterised only)
- [ ] Workflow log written to /docs/yyyyMMdd_task.md
- [ ] No secrets hardcoded
- [ ] Dependencies pinned and lockfile updated
- [ ] Tests added / updated and passing
- [ ] NSFW rules followed (or N/A)
- [ ] Agentic loop limits not exceeded
```

---

*This file is the authoritative policy source for all AI agents in this repository.  
Human engineers may override individual rules for a specific task by adding an explicit `# AGENT-OVERRIDE: <rule> — <reason>` comment in the task description, countersigned by a team lead.*
