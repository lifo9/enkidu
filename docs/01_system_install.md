# Base system installation
Installs Debian with root on ZFS using Ansible.

❗ **It assumes mirrored (two) root drives.** ❗

❗️ **It will wipe the installation target drives, co be careful** ❗

## Prerequisites
1. Have a server with IPMI access configured, connected to the internet.
2. Have `Ansible` installed on your local machine (`brew install ansible`).
3. Make sure you have `sshpass` installed (Debian live CD comes with password authentication).
4. Make sure all required environment variables are set - see [`.envrc.example`](../ansible/01_system_install/.envrc.example).\
[Direnv](https://github.com/direnv/direnv) is strongly recommended (`brew install direnv`).
5. Remove the installation host from `~/.ssh/known_hosts` if it was previously set there (to avoid SSH verification conflicts).
6. Remember to open firewall ports for both `$SERVER_PORT` and `$CHROOT_PORT`.

## Installation
1. Download the [latest Debian stable release](https://cdimage.debian.org/mirror/cdimage/release/current-live/amd64/iso-hybrid/). Make sure to download the live version (e.g. `debian-live-12.1.0-amd64-standard.iso`).
2. Verify the image (compare checksums with the official ones).
   ```shell
   shasum -a 512 /path/to/iso
   ```
3. Start the IPMI KVM and mount the image.
4. Boot to live session.
5. Install ssh server (adjust `$SERVER_PORT` to your preference).
   ```shell
   sudo apt update && sudo apt install -y openssh-server
   echo "Port $SERVER_PORT" | sudo tee -a /etc/ssh/sshd_config
   sudo service ssh restart
   ```
6. Run the Ansible playbook.
   ```shell
   ansible-playbook site.yaml -i inventory.yaml
   ```
7. Remove the installation host from  `~/.ssh/known_hosts`.
8. You can login to the server (remember to first unlock the encrypted drive via Dropbear SSH).
9. Create initial snapshots:
   ```shell
   sudo zfs snapshot -r bpool@install
   sudo zfs snapshot -r rpool@install
   ```

🎉 You can continue to [system configuration](./02_system_config.md).
