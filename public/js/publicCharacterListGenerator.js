httpRequest = new XMLHttpRequest();

httpRequest.onreadystatechange = function() {
    if (httpRequest.readyState === XMLHttpRequest.DONE) {
        const data = httpRequest.response;
        let characters = JSON.parse(data.replace(/&quot;/ig,'"'));
        let characterList = document.getElementById('characterList');

        for (let i = 0; i < characters.length; i++) {
            let game = gameGenerator(characters[i]);
            characterList.innerHTML += characterHtmlGenerator(characters[i], game);
        }
    }
};

httpRequest.open('POST', '../../joueur/character-list-json', true);
httpRequest.send();

function gameGenerator(character) {
    let gameName = '';
    if (character.game !== null) {
        let lowercase = character.game.name.toLowerCase().slice(1);
        let first = character.game.name.charAt(0).toUpperCase();
        gameName = first + lowercase;
    } else {
        gameName = 'Aucune';
    }
    return gameName
}

function characterHtmlGenerator(character, gameName) {
    let characterHtml = `<tr>
                    <td>${character.id}</td>
                    <td>${character.firstName} ${character.lastName}</td>
                    <td>
                        ${gameName}
                    </td>
                    <td>
                        <a href="/joueur/voir-personnage/${character.id}" role="button" class="btn
                    btn-info">Détail</a>
                    </td>
                    <td>
                        <a href="/joueur/contacter-personnage/${character.mp}" role="button" class="btn
                    btn-info">Contacter</a>
                    </td>
                </tr>`;
    return characterHtml;
}