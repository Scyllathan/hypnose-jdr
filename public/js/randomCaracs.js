function randomCaracs() {
    let stamina = document.getElementById('character_stamina');
    let strength = document.getElementById('character_strength');
    let agility = document.getElementById('character_agility');
    let speed = document.getElementById('character_speed');
    let intelligence = document.getElementById('character_intelligence');
    let resilience = document.getElementById('character_resilience');
    let charisma = document.getElementById('character_charisma');
    let luck = document.getElementById('character_luck');

    let caracs = [stamina, strength, agility, speed, intelligence, resilience, charisma, luck];
    let caracsValues = [];
    let add = 0;

// Boucle pour déterminer aléatoirement les valeurs des caracs
    for (let i = 0; i < 8; i++) {
        caracsValues.push(Math.round(Math.random() * 10));
    }
    const remainings = 80 - caracsValues.reduce((previousValue, currentValue) => previousValue + currentValue, 0);
    if ( remainings % 8 === 0 ) {
        add = remainings / 8;
    } else {
        add = Math.floor(remainings / 8);
        let modulo = remainings % 8;
        let i = 0;
        while ( modulo > 0 ) {
            caracsValues[i] += 1;
            modulo--;
            i++;
        }
    }
    for (let i=0; i < 8; i++) {
        caracsValues[i] += add;
    }
// Boucle pour attribuer les valeurs de caracsValues aux différentes caracs
    for (i = 0 ; i < 8; i++) {
        caracs[i].value = caracsValues[i];
    }
}
