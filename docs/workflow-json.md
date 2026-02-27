# Workflow JSON

## Minimal schema

```json
{
  "steps": [
    {
      "step_key": "string",
      "step_name": "string",
      "approvers": [
        5,
        { "user_id": 8 },
        { "by_role": "Approver" }
      ],
      "parallel": true,
      "rule": "any"
    }
  ]
}
```

## Supported fields
- `step_key`: unique step identifier.
- `step_name`: human-readable step label.
- `approvers`: list of approvers as user id, `{ "user_id": <id> }`, or `{ "by_role": "Approver" }`.
- `parallel`: `true` creates multiple tasks in the same step immediately; `false` assigns one-by-one.
- `rule`: `any` or `all`.

## Example A (`any` + parallel)

```json
{
  "steps": [
    {
      "step_key": "security-review",
      "step_name": "Security Review",
      "approvers": [{ "by_role": "Approver" }],
      "parallel": true,
      "rule": "any"
    },
    {
      "step_key": "owner-signoff",
      "step_name": "Owner Signoff",
      "approvers": [{ "by_role": "ProcessOwner" }],
      "parallel": false,
      "rule": "all"
    }
  ]
}
```

## Example B (`all` + parallel)

```json
{
  "steps": [
    {
      "step_key": "finance-gate",
      "step_name": "Finance Gate",
      "approvers": [{ "user_id": 4 }, { "user_id": 5 }],
      "parallel": true,
      "rule": "all"
    },
    {
      "step_key": "final-owner",
      "step_name": "Final Owner Approval",
      "approvers": [{ "by_role": "ProcessOwner" }],
      "parallel": true,
      "rule": "any"
    }
  ]
}
```

## Engine behaviour
1. Draft -> submitted triggers workflow lookup and first step task creation.
2. `any` step: first approval completes step, remaining pending tasks in that step are marked `skipped`.
3. `all` step: all approvers must approve.
4. Any reject immediately sets request to `rejected` and skips all pending tasks.
5. When last step completes, request becomes `approved` and `decided_at` is set.
