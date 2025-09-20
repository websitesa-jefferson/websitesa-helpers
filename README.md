# websitesa-helpers

git init
git add .
git commit -m "feat: initial helpers package"
git branch -M main
git remote add origin git@github.com:GTEC/yii2-helpers.git
git push -u origin main

# Tags

~~~~
git tag --list
~~~~
Lista todas as tags existentes no repositório local. Mostra versões e pontos de referência criados anteriormente.

~~~~
git tag -d v1.0.0
~~~~
Remove a tag chamada v1.0.0 do repositório local (não remove do remoto).

~~~~
git tag -f 1.0.0 8dc8950
~~~~
Força a criação ou atualização da tag v1.0.0 para apontar para o commit de hash 8dc8950. Se a tag já existir, ela será sobrescrita.

~~~~
git tag v1.0.0
~~~~
Cria uma nova tag chamada v1.0.0 no commit atual (HEAD). Essa tag é apenas local até ser enviada para o repositório remoto.

~~~~
git push origin v1.0.0
~~~~
Envia a tag v1.0.0 do repositório local para o repositório remoto origin, tornando-a acessível para outros usuários.

~~~~
git push origin --delete v1.0.0
~~~~
Remove a tag v1.0.0 do repositório remoto chamado origin.

~~~~
git ls-remote --tags origin
~~~~
Lista todas as tags que estão no remoto origin, junto com o hash do commit para o qual cada uma aponta.
