#!/bin/bash
set -eu

mkdir -p ~/.ssh/
echo -n "${SSH_PRIVATE_KEY_BASE_64}" | base64 -d >~/.ssh/id_rsa
chmod 0600 ~/.ssh/id_rsa

eval $(ssh-agent)
ssh-add ~/.ssh/id_rsa

bw config server $BW_SERVER
bw login --apikey

last_commit=$(git rev-parse HEAD)
prev_commit=$(git rev-parse HEAD~1)
for directory in services/*; do
  if ! git diff --quiet $prev_commit $last_commit -- $directory; then
    printf "\nChanges detected in $directory\n"
    cd $directory
    source .envrc >/dev/null 2>&1
    ansible-playbook site.yaml -i inventory.yaml
    cd ../../
  fi
done
