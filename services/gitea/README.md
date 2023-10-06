## Renovate Bot
1. Create renovatebot account in Gitea
2. Generate token for the account. Give the following permissions:
     - **repo**: Read and Write
     - **user**: Read
     - **issue**: Read and Write
     - **organization**: Read
     - **email**: Read
     - **misc**: Read
3. Enable Actions for the repository (in advanced settings)
4. Create new repository secrets:
    - `RENOVATE_TOKEN` (above token)
    - `RENOVATE_ENDPOINT` (`https://{domain}/api/v1`)
    - `RENOVATE_GIT_AUTHOR` (e.g. `Renovate Bot <renovate-bot@gitea.com>`)
    - `GH_COM_TOKEN` (token for GitHub.com)
5. Add `renovatebot` as colaborator to the repository
