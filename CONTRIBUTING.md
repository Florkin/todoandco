# Contribution

Veuillez prendre connaissance de ce document afin de suivre facilement le processus de contribution.

## Issues
[Issues](https://github.com/Florkin/todoandco/issues) est le canal idéal pour les rapports de bug, les nouvelles fonctionnalités ou pour soumettre une `pull requests`, cependant veillez a bien respecter les restrictions suivantes :
* N'utiliser par ce canal pour vos demandes d'aide personnelles (utilisez [Stack Overflow](http://stackoverflow.com/)).
* Il est interdit d'insulter ou d'offenser d'une quelconque manière en commentaire d'un `issue`. Respectez les opinions des autres, et restez concentré sur la discussion principale.

## Rapport de bug
Un bug est une erreur concrète, causée par le code présent dans ce `repository`.

Guide :
1. Assurez-vous de ne pas créer un rapport déjà existant, pensez à utiliser [le système de recherche](https://github.com/TBoileau/iletaitunefoisundev/issues).
2. Vérifiez que le bug n'est pas déjà corrigé, en essayant sur la dernière version du code sur la branche `master`.
3. Isoler le problème permet de créer un scénario de test simple et identifiable.

## Nouvelle fonctionnalité
Il est toujours apprécié de proposer de nouvelles fonctionnalités. Cependant, prenez le temps de réfléchir, assurez-vous que cette fonctionnalité correspond bien aux objectifs du projet.

C'est à vous de présenter des arguments solides pour convaincre les développeurs du projet des bienfaits de cette fonctionnalité.

## Pull request
De bonnes `pull requests` sont d'une grande aide. Elles doivent rester dans le cadre du projet et ne doit pas contenir de `commits` non lié au projet.

Veuillez demander avant de poster votre `pull request`, autrement vous risquez de passer gaspiller du temps de travail car l'équipe projet ne souhaite pas intégrer votre travail.

Suivez ce processus afin de proposer une `pull request` qui respecte les bonnes pratiques :
1. [Fork](http://help.github.com/fork-a-repo/) le projet, clonez votre `fork` et configurez les `remotes`:
    ```
    git clone https://github.com/<your-username>/<repo-name>
    cd todoandco
    git remote add upstream https://github.com/Florkin/todoandco
    ```
2. Si vous avez cloné le projet il y a quelques temps, pensez à récupérer les dernières modifications depuis `upstream`:
    ```
    git checkout master
    git pull upstream master
    ``` 
3. Créez une nouvelle branche qui contiendra votre fonctionnalité, modification ou correction :
    * Pour une nouvelle fonctionnalité ou modification :
        ```
        git checkout master
        git checkout -b feature/<#issue_number>/<feature-name>
        ```
    * Pour une nouvelle correction :
        ```
        git checkout master
        git checkout -b hotfix/<#issue_number>/<feature-name>
        ```
   
4. `Commit` vos changements, veuillez à respecter la convention de nommage de vos `commits` de la manière suivante :
    ```
   [<nom_de_la_branche>] <description> 
    ```

5. Poussez votre branche sur votre `repository` :
    ```
    git push origin <branch-name> 
    ```
6. Ouvrez une nouvelle `pull request` avec un titre et une description précises.
Le titre doit contenir le numéro de l'issue.

