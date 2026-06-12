# F3 RVA Big Data

## Local Development
To run the application locally using the built-in PHP server:
```bash
php -S localhost:8000 -d xdebug.mode=debug -d short_open_tag=true
```

For database access and other configuration parameters, a `settings.php` file is required in the root directory (this file is gitignored).

## Deployment
This project uses GitHub Actions to automate deployments to the remote hosting environment using **GitHub Environments** (`development` and `production`).

- **Development Deployment:** Triggered on commits pushed to the `main` branch or manual workflow dispatch.
- **Production Deployment:** Triggered when a new GitHub Release is published (currently stubbed/prepared but disabled).
- **Workflow:** The workflow prepares the SSH key, bundles the codebase (excluding local settings and development files), uploads the archive to remote hosting via `scp`, and extracts it on the host via `ssh`.

### GitHub Environment Secrets Setup
Instead of configuring global repository secrets, configure the following secrets inside each specific GitHub Environment (**Settings > Environments > [environment_name] > Environment secrets**):

| Secret Name | Description |
|---|---|
| `REMOTE_HOST` | The server host (IP address or domain) of your remote hosting for that environment. |
| `REMOTE_USER` | The SSH username for connecting to the server. |
| `SERVER_SSH_KEY` | The private SSH key matching the public key authorized on the server. |
| `REMOTE_TARGET` | The absolute path on the hosting server where files should be extracted (e.g., `/home/username/public_html/dev` for dev, or `/home/username/public_html/` for prod). |
| `REMOTE_PORT` | *(Optional)* The SSH port to connect to (defaults to `22`). |

### Configuration (`settings.php`) on Server
Since `settings.php` is ignored by Git, it is not bundled or overwritten during deployment. Make sure you manually create/maintain the appropriate environment-specific `settings.php` file on your remote hosting folders (`dev` and `production` paths).