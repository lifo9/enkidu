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
    - `DOCKER_HUB_USERNAME` (username for Docker Hub)
    - `DOCKER_HUB_PASSWORD` (token for Docker Hub)
5. Add `renovatebot` as colaborator to the repository

## Auto Deploy
1. Generate SSH key for the deploy user:
    - `ssh-keygen -t ed25519 -C "deploy@{domain}" -f ./deploy-key`
    - Save ^ to Bitwarden (`cat ./deploy-key | base64`)
2. Create new repository secret:
    - `BW_CLIENTID` (Bitwarden client ID)
    - `BW_CLIENTSECRET` (Bitwarden client secret)
    - `BW_SERVER` (Bitwarden server URL)
    - `BW_PASSWORD` (Bitwarden password for unlocking vault)
    - `SSH_PRIVATE_KEY_BASE_64` (SSH private key for the server in base64)
    - `LOCAL_REGISTRY` (domain of the Gitea instance)
    - `PAT_TOKEN` (personal access token for the Gitea instance)
3. Add deploy key to authorized keys on the server
    - `ssh-copy-id -i ./deploy-key.pub {user}@{domain}`
