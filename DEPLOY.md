# Deploy no Vercel

## Pré-requisitos

1. Conta no [Vercel](https://vercel.com) (free tier)
2. Conta no [GitHub](https://github.com)
3. Banco de dados MySQL (recomendo [PlanetScale](https://planetscale.com) free tier ou [Turso](https://turso.tech) para SQLite)

## Passo 1: Criar repositório no GitHub

```bash
cd "C:\Users\lipin\OneDrive\Área de Trabalho\Nova pasta\rifa-app"
git add .
git commit -m "Initial commit - Rifa platform"
git remote add origin https://github.com/SEU-USUARIO/rifa-app.git
git push -u origin main
```

## Passo 2: Conectar ao Vercel

1. Acesse [vercel.com/new](https://vercel.com/new)
2. Importe o repositório do GitHub
3. Configure:
   - **Framework Preset:** Other
   - **Build Command:** `composer install --prefer-dist --no-dev && npm install && npm run build`
   - **Output Directory:** `public`
   - **Install Command:** `composer install --prefer-dist --no-dev`

## Passo 3: Variáveis de Ambiente

No painel do Vercel, vá em **Settings > Environment Variables** e adicione:

```
APP_NAME=RifaApp
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-app.vercel.app
APP_KEY=base64:YOUR_KEY_HERE

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=rifa
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

## Passo 4: Banco de Dados

### Opção A: PlanetScale (MySQL, recomendo)
1. Crie conta gratuita em [planetscale.com](https://planetscale.com)
2. Crie um banco de dados
3. Copie a URL de conexão para as variáveis `DB_*`

### Opção B: Turso (SQLite, mais simples)
1. Instale o CLI: `curl -sSfL https://get.tur.so/install.sh | bash`
2. Crie banco: `turso db create rifa-db`
3. Copie a URL: `turso db show rifa-db --url`

## Passo 5: Rodar Migrations

```bash
# Via Vercel CLI
vercel env pull .env.local
php artisan migrate --force
```

Ou adicione um script de build no `vercel.json`:
```json
{
  "buildCommand": "composer install --prefer-dist --no-dev && php artisan migrate --force && npm install && npm run build"
}
```

## Passo 6: Acessar

Após o deploy, acesse: `https://seu-app.vercel.app`

## Notas Importantes

- O Vercel não suporta filas (queues) - use `QUEUE_CONNECTION=sync` para demonstração
- Sessões são gravadas no banco - configure `SESSION_DRIVER=database`
- Para upload de arquivos, use S3 ou similar (o filesystem é read-only no Vercel)
