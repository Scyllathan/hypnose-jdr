function withoutGameCharacters() {
    if (httpRequest.readyState === XMLHttpRequest.DONE) {
        const data = httpRequest.response;
        let dataReformat = JSON.parse(data.replace(/&quot;/ig,'"'));
        let characters = dataReformat;
        let characterList = document.getElementById('characterList');
        characterList.innerHTML = '';

        for (let i = 0; i < characters.length; i++) {
            if (characters[i].game === null) {
                let game = gameGenerator(characters[i]);
                characterList.innerHTML += characterHtmlGenerator(characters[i], game);
            }
        }
    } else {
        // pas encore prête
    }
}

function allCharacters() {
    if (httpRequest.readyState === XMLHttpRequest.DONE) {
        const data = httpRequest.response;
        let dataReformat = JSON.parse(data.replace(/&quot;/ig,'"'));
        let characters = dataReformat;
        let characterList = document.getElementById('characterList');
        characterList.innerHTML = '';

        for (let i = 0; i < characters.length; i++) {
            let game = gameGenerator(characters[i]);
            characterList.innerHTML += characterHtmlGenerator(characters[i], game);
        }
    } else {
        // pas encore prête
    }
}

function characterById() {
    const data = httpRequest.response;
    let dataReformat = JSON.parse(data.replace(/&quot;/ig,'"'));
    let characters = dataReformat;
    let characterList = document.getElementById('characterList');
    characterList.innerHTML = '';

    let idFilter = document.getElementById('idFilter').value.toString()
    let text = '';

    for (let i = 0; i < characters.length; i++) {
        text = characters[i].id.toString();

        if (text.indexOf(idFilter) > -1) {
            let game = gameGenerator(characters[i]);
            characterList.innerHTML += characterHtmlGenerator(characters[i], game);
        }
    }
}

function characterByName() {
    const data = httpRequest.response;
    let dataReformat = JSON.parse(data.replace(/&quot;/ig,'"'));
    let characters = dataReformat;
    let characterList = document.getElementById('characterList');
    characterList.innerHTML = '';

    let nameFilter = document.getElementById('nameFilter').value.toString().toLowerCase();
    let text = '';

    for (let i = 0; i < characters.length; i++) {
        text = (characters[i].firstName + ' ' + characters[i].lastName).toString().toLowerCase();

        if (text.indexOf(nameFilter) > -1) {
            let game = gameGenerator(characters[i]);
            characterList.innerHTML += characterHtmlGenerator(characters[i], game);
        }
    }
}