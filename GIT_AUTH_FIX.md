# Git Authentication Fix Guide

## Problem
You're authenticated as `devvurly-gif` but trying to push to `maigaless/mobileparts.git`.

## Solutions

### Option 1: Use Personal Access Token (PAT) - Recommended for HTTPS

1. **Create a Personal Access Token on GitHub:**
   - Go to GitHub.com → Settings → Developer settings → Personal access tokens → Tokens (classic)
   - Click "Generate new token (classic)"
   - Give it a name like "Laravel Project"
   - Select scopes: `repo` (full control of private repositories)
   - Generate token and **COPY IT** (you won't see it again)

2. **Update Git Credentials:**
   - Windows will prompt for credentials on next push
   - Username: `maigaless` (or the account that owns the repo)
   - Password: **Paste your Personal Access Token** (not your GitHub password)

3. **Clear old credentials first:**
   ```bash
   # Clear stored credentials
   git credential-manager erase
   ```
   Then when pushing, enter the new token.

### Option 2: Switch to SSH (More Secure)

1. **Change remote URL to SSH:**
   ```bash
   git remote set-url origin git@github.com:maigaless/mobileparts.git
   ```

2. **Check if SSH key exists:**
   ```bash
   ls ~/.ssh/id_rsa.pub
   # or on Windows Git Bash:
   ls ~/.ssh/id_*.pub
   ```

3. **If no SSH key, generate one:**
   ```bash
   ssh-keygen -t ed25519 -C "dev.vurly@gmail.com"
   # Press Enter for default location
   # Enter passphrase (optional but recommended)
   ```

4. **Add SSH key to GitHub:**
   ```bash
   # Copy public key
   cat ~/.ssh/id_ed25519.pub
   # Copy the output
   ```
   - Go to GitHub.com → Settings → SSH and GPG keys
   - Click "New SSH key"
   - Paste the key and save

5. **Test SSH connection:**
   ```bash
   ssh -T git@github.com
   ```

### Option 3: Update Windows Credential Manager

1. **Open Windows Credential Manager:**
   - Press `Win + R`, type `control /name Microsoft.CredentialManager`
   - Or: Control Panel → Credential Manager → Windows Credentials

2. **Find GitHub credentials:**
   - Look for `git:https://github.com` or similar
   - Edit or remove it

3. **On next git push, enter:**
   - Username: `maigaless` (repository owner)
   - Password: Your Personal Access Token

### Option 4: Use GitHub CLI (gh)

If you have GitHub CLI installed:
```bash
gh auth login
# Follow prompts to authenticate
```

---

## Quick Fix Commands

### For HTTPS with Personal Access Token:
```bash
# 1. Clear old credentials
git credential-manager erase

# 2. Change remote (if needed - already correct)
git remote set-url origin https://github.com/maigaless/mobileparts.git

# 3. Try pushing (you'll be prompted for credentials)
git push -u origin main
# Username: maigaless
# Password: [your-personal-access-token]
```

### For SSH:
```bash
# 1. Change remote to SSH
git remote set-url origin git@github.com:maigaless/mobileparts.git

# 2. Test connection
ssh -T git@github.com

# 3. Push
git push -u origin main
```

---

## Important Notes

- **Personal Access Tokens expire**: You may need to regenerate them periodically
- **SSH keys are permanent**: Once set up, they work indefinitely (unless revoked)
- **Repository permissions**: Make sure your account has push access to the repository
- **Two-factor authentication**: If 2FA is enabled on GitHub, you MUST use PAT or SSH (password won't work)

---

## Verify Access

Check if you have push access:
```bash
# For HTTPS, try:
git ls-remote origin

# For SSH, try:
ssh -T git@github.com
```

---

## Troubleshooting

**Error: "Permission denied (publickey)"**
- SSH key not added to GitHub
- Wrong SSH key being used
- Solution: Add SSH key to GitHub or check `~/.ssh/config`

**Error: "403 Forbidden"**
- Wrong credentials being used
- Token expired or revoked
- Solution: Generate new PAT or check account access

**Error: "Repository not found"**
- Repository doesn't exist
- You don't have access
- Solution: Check repository URL and your access permissions

