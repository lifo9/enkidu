# System configuration

1. Change directory to [ansible/02_system_config](../ansible/02_system_config/).
2. Ensure that you have all environment variables loaded.
3. Run Ansible playbook:
    ```bash
    ansible-playbook site.yaml -i inventory.yaml
    ```
4. It will reboot the system at the end, so you will need to unlock it before continuing.

🎉 You can continue to [storage setup](./03_storage.md).
