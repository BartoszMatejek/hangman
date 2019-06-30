const loader = document.getElementById('loader');
const guessInput = document.getElementById('guessInput');
const guessButton = document.getElementById('guessButton');
const canvas = document.getElementById('canvas');

const showLoader = () => loader.style.display = null;
const hideLoader = () => loader.style.display = "none";

guessButton.disabled = true;

const scale = item => item.map(values => values.map(value => value * SCALE_FACTOR));

const SCALE_FACTOR = 16;
const LINE_WIDTH = 5;

const hanger = [
    [2, 16, 8, 16],
    [5, 16, 5, 1],
    [5, 1, 10, 1],
    [5, 3, 7, 1],
    [10, 1, 10, 2],
];

const head = [
    [10, 3, 1],
];

const body = [
    [10, 5, 10, 4], // neck
    [10, 5, 10, 10], // body
    [10, 5, 8, 8], // left arm
    [10, 5, 12, 8], // right arm
    [10, 10, 8, 14], // left leg
    [10, 10, 12, 14], // right leg
];

const hangman = {
    hanger: scale(hanger),
    head: scale(head),
    body: scale(body),
};

const gameState = {
    letters: "",
    wrong_letters: [],
    remaining_guesses: 0,
    message: "",
	points: 0,
	success: false,
	correct_word: "",
	name: ""
};

const renderLetters = () => {
    const newLettersContainer = document.createElement("div");
    newLettersContainer.className = 'letters-container';
    
    gameState.letters.split('').forEach(letter => {
        const element = document.createElement("div");
        element.className = letter !== ' ' ? 'letters-item' : 'letters-item letters-item-space';
        element.appendChild(document.createTextNode(letter !== '_' ? letter : ' '));
        newLettersContainer.appendChild(element);
    });

    document.getElementById('lettersContainer').replaceWith(newLettersContainer);
    newLettersContainer.id = 'lettersContainer';
};

const renderWrongLetters = () => {
    document.getElementById('wrongLetters').innerText = `Nietrafione litery: ${gameState.wrong_letters.join(", ")}`;
}

const renderPoints = () => {
    document.getElementById('points').innerText = `Zdobyte punkty: ${gameState.points}`;
}

if (canvas.getContext) {
    const context = canvas.getContext('2d');

    const drawCircle = ([x, y, radius]) => {
        context.lineWidth = LINE_WIDTH;
        context.beginPath();
        context.arc(x, y, radius, 0, Math.PI * 2);
        context.stroke();
    }

    const drawLine = ([xs, ys, xe, ye]) => {
        context.lineWidth = LINE_WIDTH;
        context.beginPath();
        context.moveTo(xs, ys);
        context.lineTo(xe, ye);
        context.stroke();
    };

    const drawHangman = () => {
        context.clearRect(0, 0, canvas.width, canvas.height);

        hangman.hanger.forEach(line => drawLine(line));

        const { remaining_guesses } = gameState;

        const rg = hangman.body.length - (remaining_guesses > hangman.body.length ? hangman.body.length : remaining_guesses);

        if (rg > 0) {
            hangman.head.forEach(circle => drawCircle(circle));
        }

        for (let i = 0; i < rg; i++) {
            drawLine(hangman.body[i]);
        }
    };

    const doEverything = () => {
		console.log(gameState.success);
		console.log(gameState.remaining_guesses);
		if (gameState.success == "true"){
            renderLetters();
			renderPoints();
			alert('Gratulacje, odgadłeś słowo: '+gameState.correct_word);
			gameState.success = "false";
			
			getGame();
		} else{
			if (gameState.remaining_guesses === 0) {
			renderWrongLetters();
			renderPoints();
            alert("Gra przegrana :( . Spróbuj jeszcze raz!");
			guessButton.disabled = false;
            guessButton.innerText = 'Try again!';

        } else {
            guessButton.innerText = 'Guess';
            guessButton.disabled = true;
        }
		}
        
		
        if (gameState.message != '') {
            alert(gameState.message);
            gameState.message = '';
        } else {
            drawHangman();
            renderLetters();
			renderPoints();
            renderWrongLetters();
        }
        
        guessInput.value = null;
        guessInput.focus();
        
    };

    const getGame = () => {
        fetch('https://sggw.000webhostapp.com/getGame.php')
            .then(response => response.json())
            .then(json => Object.assign(gameState, json))
            .then(() => doEverything())
            .catch(error => alert(`Error while trying to load game, refresh page. Reason: ${error}`))
            .finally(() => hideLoader(loader));
    }

    const makeGuess = () => {
        if (gameState.remaining_guesses <= 0) {
            getGame();
            return;
        }

        const { value } = guessInput;

        if (value.length >= 0 && !guessButton.disabled) {
            showLoader(loader);

            fetch(
                "https://sggw.000webhostapp.com/getGame.php",
                {
                    method: "POST",
                    body: JSON.stringify({ letter: value.substring(0, 1) }),
                    headers: { 'Content-Type': 'application/json;charset=UTF-8' }
                }
            )
                .then(response => response.json())
                .then(json => Object.assign(gameState, json))
                .then(() => doEverything())
                .catch(error => alert(`Error while trying to make guess, try again or refresh page. Reason: ${error}`))
                .finally(() => hideLoader(loader));
        }
    }

    guessInput.addEventListener("input", (event) => {
        const { value } = event.target;
        if (value.length > 1) {
            guessInput.value = value.substring(1, 2);   
        }
        guessButton.disabled = !(guessInput.value.length >= 1);
    });

    guessInput.addEventListener("keypress", (event) => {
        if (event.key === "Enter") {
            makeGuess();
        }
    });

    guessButton.addEventListener("click", () => makeGuess());

    getGame();
}
