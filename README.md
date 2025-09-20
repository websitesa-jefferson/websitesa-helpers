## 📌 websitesa-helpers

```bash
git init
git add .
git commit -m "feat: initial helpers package"
git branch -M main
git remote add origin git@github.com:GTEC/yii2-helpers.git
git push -u origin main
```

## 📌 Guia de Tags no Git

Este documento descreve os principais comandos para **criar, alterar, enviar e excluir tags** no Git, seguindo boas práticas de versionamento semântico (`MAJOR.MINOR.PATCH`).

---

## 🔹 Listar tags existentes
```bash
git tag --list
```

---

## 🔹 Criar tags

### Tag simples no último commit (HEAD)
```bash
git tag v1.0.0
```

### Tag anotada (recomendada – inclui autor, data e mensagem)
```bash
git tag -a v1.0.0 -m "Versão 1.0.0 - release inicial"
```

### Tag em um commit específico
```bash
git tag v1.0.0 <commit-hash>
```

### Enviar uma tag para o remoto
```bash
git push origin v1.0.0
```

### Enviar todas as tags de uma vez
```bash
git push origin --tags
```

---

## 🔹 Atualizar/Mover tags

### Mover tag para o commit atual
```bash
git tag -f v1.0.0
```

### Mover tag para um commit específico
```bash
git tag -f v1.0.0 <commit-hash>
```

### Atualizar a tag também no remoto
```bash
git push origin -f v1.0.0
```

---

## 🔹 Deletar tags

### Deletar tag localmente
```bash
git tag -d v1.0.0
```

### Deletar tag no remoto
```bash
git push origin --delete v1.0.0
```

Ou:
```bash
git push origin :refs/tags/v1.0.0
```

---

## 🔹 Fluxo de versionamento SemVer

- **MAJOR (X.0.0):** mudanças que quebram compatibilidade.
- **MINOR (0.X.0):** novas funcionalidades sem quebrar compatibilidade.
- **PATCH (0.0.X):** correções de bugs.

### Exemplo de releases
```bash
# Primeira versão estável
git tag -a v1.0.0 -m "Versão 1.0.0 - release inicial"
git push origin v1.0.0

# Patch (bugfix)
git tag -a v1.0.1 -m "Correção de bug na autenticação"
git push origin v1.0.1

# Minor (novas funcionalidades)
git tag -a v1.1.0 -m "Novos relatórios adicionados"
git push origin v1.1.0

# Major (mudanças incompatíveis)
git tag -a v2.0.0 -m "API reestruturada - breaking changes"
git push origin v2.0.0
```

---

📌 **Dica:** sempre crie tags na branch principal (`main` ou `master`), garantindo que representem uma versão estável do projeto.
