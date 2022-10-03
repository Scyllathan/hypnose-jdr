function randomAge() {
    let charAge = document.getElementById('character_age');
    let randomAge = 0;

    while (randomAge < 15) {
        randomAge = Math.ceil(Math.random() * 60);
    }

    charAge.value = randomAge;
}

function randomDisease() {
    let disease = document.getElementById('character_disease');
    let diseaseList = ['Pyrophobie', 'Acrophobie', 'Coulrophobie', 'Ablutophobie', 'Achluophobie', 'Aérophobie', 'Algophobie', 'Anthophobie', 'Aquaphobie', 'Astraphobie', 'Bacillophobie', 'Bélénophobie', 'Claustrophobie', 'Gymnophobie', 'Haptophobie', 'Hématophobie', 'Machairophobie', 'Mégalophobie', 'Nécrophobie', 'Ochlophobie', 'Paranoïa', 'Dépression', 'Troubles dissociatifs de la personnalité', 'Mythomanie', 'Kleptomanie', 'Mégalomanie', 'Pyromanie', 'Trouble obsessionnel-compulsif', 'Anorexie', 'Hypochondrie', 'Nymphomanie', 'Pédiophobie', 'Phobophobie', 'Somniphobie', 'Téléphonophobie', 'Trypophobie', 'Ailurophobie', 'Arachnophobie', 'Cynophobie', 'Ornithophobie'];
    let randomDiseaseIndex = Math.floor(Math.random() * diseaseList.length);
    disease.value = diseaseList[randomDiseaseIndex];
}

function randomLastName() {
    let lastName = document.getElementById('character_lastName');
    let lastNameList = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torez', 'Nguyen', 'Hill', 'Flores', 'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell', 'Carter', 'Roberts'];
    let randomLastNameIndex = Math.floor(Math.random() * lastNameList.length);
    lastName.value = lastNameList[randomLastNameIndex];
}

function randomFirstName() {
    let firstName = document.getElementById('character_firstName');
    let firstNameList = ['James', 'John', 'Robert', 'Michael', 'William', 'David', 'Richard', 'Charles', 'Joseph', 'Thomas', 'Christopher', 'Daniel', 'Paul', 'Mark', 'Donald', 'George', 'Kenneth', 'Steven', 'Edward', 'Brian', 'Ronald', 'Anthony', 'Kevin', 'Jason', 'Matthew', 'Gary', 'Timothy', 'Jose', 'Mary', 'Patricia', 'Linda', 'Barbara', 'Elizabeth', 'Jennifer', 'Maria', 'Susan', 'Margaret', 'Dorothy', 'Lisa', 'Nancy', 'Karen', 'Betty', 'Helen', 'Sandra', 'Donna', 'Carol', 'Ruth', 'Sharon', 'Michelle', 'Laura', 'Sarah', 'Kimberly', 'Deborah', 'Jessica', 'Shirley', 'Angela'];
    let randomFirstNameIndex = Math.floor(Math.random() * firstNameList.length);
    firstName.value = firstNameList[randomFirstNameIndex];
}

function randomIdentity() {
    randomAge();
    randomDisease();
    randomFirstName();
    randomLastName();
}