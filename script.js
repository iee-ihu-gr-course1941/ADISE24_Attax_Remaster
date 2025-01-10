document.addEventListener('DOMContentLoaded', () => {
    const gameBoard = document.getElementById('game-board');
    const leaderboard = document.getElementById('leaderboard');
    const leaderboardList = document.getElementById('leaderboard-list');
    const gameCodeDisplay = document.getElementById('game-code');
    const playerColorDisplay = document.getElementById('player-color');
    const playerPositionDisplay = document.getElementById('player-position');
    const startGameButton = document.getElementById('start-game');
    const createGameButton = document.getElementById('create-game-btn');
    const joinGameForm = document.getElementById('join-game-form');
    const gameCodeInput = document.getElementById('game-code-input');
    const playerNameInput = document.getElementById('player-name-input');
    const gameStatusMessage = document.getElementById('game-status-message');
    const refreshGameStateButton = document.getElementById('refresh-game-state');

    let game_id = null;
    let playerColor = null;
    let playerPosition = null;
    let currentPlayer = 'red'; // Red starts the game
    let gameStatus = 'waiting';
    let pollingInterval = null;

    // Generate a random 6-character game code
    function generateRandomGameCode() {
        return Math.random().toString(36).substr(2, 6).toUpperCase();
    }

    // Generate a random player name
    function generateRandomName() {
        const names = ['Player 1', 'Player 2', 'Player 3', 'Player 4'];
        return names[Math.floor(Math.random() * names.length)];
    }

    // Fetch the current game state
    function getGameState() {
        if (!game_id) return;

        fetch(`/game.php?action=get_game_state&game_id=${game_id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log("Game state fetched:", data);
                    updateBoardDisplay(data.board);

                    if (data.players.length === 2) {
                        gameStatus = "in_progress";
                        gameStatusMessage.textContent = "Both players have joined. The game is starting!";
                        startGameButton.disabled = true;
                    } else {
                        gameStatus = "waiting";
                        gameStatusMessage.textContent = "Waiting for the second player to join.";
                    }
                } else {
                    console.error("Error:", data.message);
                    alert(data.message);
                }
            })
            .catch(error => console.error("Error fetching game state:", error));
    }

    // Poll game state periodically
    function startPolling() {
        if (!pollingInterval) {
            pollingInterval = setInterval(getGameState, 2000);
        }
    }

    // Stop polling for game state
    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    }

    // Update the game board display
    function updateBoardDisplay(board) {
    console.log("Received board data:", board);

    gameBoard.innerHTML = ''; // Clear the board

    if (!board || board.length === 0) {
        console.error("Board data is empty or invalid.");
        alert("Unable to display the board. Please refresh the page.");
        return;
    }

    // Render the 7x7 board
    for (let i = 0; i < 7; i++) {
        for (let j = 0; j < 7; j++) {
            const cellDiv = document.createElement('div');
            cellDiv.classList.add('cell');

            // Default to 'white' if cell is empty
            const cellColor = board[i][j] === 'empty' ? 'white' : board[i][j];
            console.log(`Cell at (${i}, ${j}) set to color: ${cellColor}`);
            cellDiv.style.backgroundColor = cellColor;
            cellDiv.dataset.row = i;
            cellDiv.dataset.col = j;

            // Add a click handler for moves (if the game is in progress)
            if (gameStatus === 'in_progress') {
                cellDiv.addEventListener('click', () => handlePlayerMove(i, j));
            }

            gameBoard.appendChild(cellDiv);
        }
    }
    console.log("Board rendered successfully.");
}


    // Handle player click
    function handlePlayerMove(row, col) {
      if (gameStatus !== 'in_progress') {
          alert("The game has not started yet.");
          return;
      }

      const clickedCell = document.querySelector(`[data-row="${row}"][data-col="${col}"]`);
      const isOwnPiece = clickedCell && clickedCell.style.backgroundColor === currentPlayer;

      if (isOwnPiece) {
          // Highlight valid moves for the selected piece and log them
          highlightValidMoves(row, col);
      } else if (clickedCell && clickedCell.classList.contains('valid-move')) {
          // If clicking on a valid move, send the move to the server
          const adjustedRow = row + 1; // Convert to 1-based index
          const adjustedCol = col + 1; // Convert to 1-based index

          // Log the move data
          console.log(`Making move: from (${row}, ${col}) to (${adjustedRow}, ${adjustedCol})`);

          // Send the move to the server
          updateBoardAfterMove(game_id, adjustedRow, adjustedCol, currentPlayer);

          // Log the current player making the move
          console.log(`Move made by: ${currentPlayer}`);

          switchTurn(); // Switch turns after a valid move
      } else {
          alert("Invalid move. Select a valid move or your own piece.");
      }
  }



    // Highlight valid moves
     function highlightValidMoves(row, col) {
    // Clear previous highlights
    const cells = document.querySelectorAll('.cell');
    cells.forEach(cell => cell.classList.remove('valid-move', 'selected'));

    // Mark the clicked piece as selected
    const selectedCell = document.querySelector(`[data-row="${row}"][data-col="${col}"]`);
    if (!selectedCell) {
        console.error(`No cell found at row=${row}, col=${col}`);
        return;
    }

    selectedCell.classList.add('selected');

    const validMoves = [];

    // Calculate valid moves (1 or 2 steps in any direction)
    for (let r = -2; r <= 2; r++) {
        for (let c = -2; c <= 2; c++) {
            if (r === 0 && c === 0) continue; // Skip the current piece's position

            const newRow = row + r;
            const newCol = col + c;

            // Check if the move is within bounds
            if (newRow >= 0 && newRow < 7 && newCol >= 0 && newCol < 7) {
                const targetCell = document.querySelector(`[data-row="${newRow}"][data-col="${newCol}"]`);
                if (targetCell) {
                    const cellColor = targetCell.style.backgroundColor;
                    console.log(`Cell at (${newRow}, ${newCol}) has color: ${cellColor}`);

                    // Adjust comparison for empty cells
                    if (cellColor === 'white' || cellColor === '' || cellColor === 'rgba(0, 0, 0, 0)') {
                        targetCell.classList.add('valid-move');
                        validMoves.push({ row: newRow, col: newCol });
                    }
                }
            }
        }
    }

    console.log(`Valid moves for piece at (${row}, ${col}):`, validMoves);
}





    // Send move to the server
    function updateBoardAfterMove(gameId, row, col, playerColor) {
        fetch(`/game.php?action=update_board`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ game_id: gameId, board_row: row, board_column: col, player_color: playerColor })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    getGameState();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error("Error updating the board:", error));
    }

    // Switch turns
    function switchTurn() {
        currentPlayer = currentPlayer === 'red' ? 'blue' : 'red';
        gameStatusMessage.textContent = `${currentPlayer.toUpperCase()}'s turn to move.`;
    }

    // Create game
    createGameButton.addEventListener('click', async () => {
        const gameCode = generateRandomGameCode();
        const playerName = generateRandomName();

        const response = await fetch('/game.php?action=create_game', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ game_code: gameCode, player_name: playerName })
        });

        const data = await response.json();

        if (data.success) {
            game_id = data.game_id;
            gameCodeDisplay.textContent = data.game_code;
            playerColor = 'red';
            playerPosition = 'Player 1';
            playerColorDisplay.textContent = playerColor;
            playerPositionDisplay.textContent = playerPosition;

            updateBoardDisplay([
                ['red', '', '', '', '', '', 'blue'],
                ['', '', '', '', '', '', ''],
                ['', '', '', '', '', '', ''],
                ['', '', '', '', '', '', ''],
                ['', '', '', '', '', '', ''],
                ['', '', '', '', '', '', ''],
                ['blue', '', '', '', '', '', 'red']
            ]);

            document.querySelector('.game-area').style.display = 'block';
            startPolling();
        } else {
            alert(data.message);
        }
    });

    // Join game
    joinGameForm.addEventListener('submit', event => {
        event.preventDefault();

        const gameCode = gameCodeInput.value.trim();
        const playerName = playerNameInput.value.trim();

        if (!gameCode || !playerName) {
            alert("Enter both game code and player name.");
            return;
        }

        fetch(`/game.php?action=join_game&game_code=${gameCode}&player_name=${playerName}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    game_id = data.game_id;
                    playerColor = data.player_color;
                    playerPosition = 'Player 2';
                    gameCodeDisplay.textContent = gameCode;
                    playerColorDisplay.textContent = playerColor;
                    playerPositionDisplay.textContent = playerPosition;

                    document.querySelector('.game-area').style.display = 'block';
                    startPolling();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error("Error joining game:", error));
    });
});
