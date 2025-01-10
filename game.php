<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db.php'; // Assuming you have your database connection in this file

// Function to get game state
function get_game_state($game_id) {
    global $pdo;
    try {
        // Fetch the game status
        $stmt = $pdo->prepare("SELECT status FROM games WHERE id = ?");
        $stmt->execute([$game_id]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch players
        $player_stmt = $pdo->prepare("SELECT player_name, player_color FROM players WHERE game_id = ?");
        $player_stmt->execute([$game_id]);
        $players = $player_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch board data
        $board_stmt = $pdo->prepare("SELECT board_row, board_column, cell_status FROM game_board WHERE game_id = ?");
        $board_stmt->execute([$game_id]);
        $board_data = $board_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Initialize the board as a 7x7 array filled with "empty"
        $board = array_fill(0, 7, array_fill(0, 7, 'empty'));

        foreach ($board_data as $cell) {
            $row = (int)$cell['board_row'] - 1; // Convert to zero-indexed
            $col = (int)$cell['board_column'] - 1; // Convert to zero-indexed
            $board[$row][$col] = $cell['cell_status'];
        }

        // Return the game state
        if ($game) {
            return [
                'success' => true,
                'status' => $game['status'],
                'players' => $players,
                'board' => $board
            ];
        } else {
            return ['success' => false, 'message' => 'Game not found'];
        }
    } catch (Exception $e) {
        error_log('Error fetching game state: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Unable to fetch game state'];
    }
}



// Function to start the game
function start_game($game_id) {
    global $pdo;

    try {
        // Update the game status to "in_progress"
        $stmt = $pdo->prepare("UPDATE games SET status = 'in_progress' WHERE id = ?");
        $stmt->execute([$game_id]);

        return ['success' => true, 'message' => 'Game has started!'];
    } catch (Exception $e) {
        error_log('Error starting game: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Unable to start game'];
    }
}

// Function to create a new game

function create_game() {
    global $pdo;

    try {
        // Generate a random game code (6 characters)
        $game_code = strtoupper(bin2hex(random_bytes(3)));

        // Insert new game with one player
        $stmt = $pdo->prepare("INSERT INTO games (game_code, player_count, status) VALUES (?, 1, 'waiting')");
        $stmt->execute([$game_code]);

        // Get the new game ID
        $game_id = $pdo->lastInsertId();

        // Insert the first player
        $player_name = "Player 1"; // Default name
        $player_color = "red"; // Player 1 always gets red
        $stmt = $pdo->prepare("INSERT INTO players (game_id, player_name, player_color) VALUES (?, ?, ?)");
        $stmt->execute([$game_id, $player_name, $player_color]);

        // Initialize the board with 7x7 size and starting positions
        $rows = 7;
        $columns = 7;

        // Loop through and initialize all cells as 'empty'
        for ($row = 1; $row <= $rows; $row++) {
            for ($col = 1; $col <= $columns; $col++) {
                $stmt = $pdo->prepare("INSERT INTO game_board (game_id, board_row, board_column, cell_status) VALUES (?, ?, ?, 'empty')");
                $stmt->execute([$game_id, $row, $col]);
            }
        }

        // Set the starting positions for Player 1 and Player 2 in the corners of the board
        $starting_positions = [
            ['board_row' => 1, 'board_column' => 1, 'cell_status' => 'red'], // Top-left corner
            ['board_row' => 1, 'board_column' => 7, 'cell_status' => 'blue'], // Top-right corner
            ['board_row' => 7, 'board_column' => 1, 'cell_status' => 'blue'], // Bottom-left corner
            ['board_row' => 7, 'board_column' => 7, 'cell_status' => 'red']  // Bottom-right corner
        ];

        // Insert the starting positions into the game_board table
        foreach ($starting_positions as $position) {
            $stmt = $pdo->prepare("UPDATE game_board SET cell_status = ? WHERE game_id = ? AND board_row = ? AND board_column = ?");
            $stmt->execute([$position['cell_status'], $game_id, $position['board_row'], $position['board_column']]);
        }

        return ['success' => true, 'game_id' => $game_id, 'game_code' => $game_code, 'message' => 'You are the first player. Waiting for the second player.'];
    } catch (Exception $e) {
        error_log('Error creating game: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Unable to create game'];
    }
}


// Function to join an existing game
function join_game($game_code, $player_name) {
    global $pdo;

    try {
        // Check if the game code exists
        $stmt = $pdo->prepare("SELECT * FROM games WHERE game_code = ?");
        $stmt->execute([$game_code]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($game) {
            // Check if the game is already full
            if ($game['player_count'] >= 2) {
                return ['success' => false, 'message' => 'Game is full'];
            }

            // Determine the color for the new player
            if ($game['player_count'] == 1) {
                // If this is Player 2, assign 'blue', otherwise, Player 1 will get 'red'
                $player_color = 'blue';
            } else {
                $player_color = 'red'; // Player 1 gets 'red'
            }

            // Increment the player count first
            $stmt = $pdo->prepare("UPDATE games SET player_count = player_count + 1 WHERE game_code = ?");
            $stmt->execute([$game_code]);

            // Insert the new player into the players table
            $stmt = $pdo->prepare("INSERT INTO players (game_id, player_name, player_color) VALUES (?, ?, ?)");
            $stmt->execute([$game['id'], $player_name, $player_color]);

            // If both players are in, start the game by updating the game status
            if ($game['player_count'] + 1 === 2) {
                // Set the game status to "in_progress"
                $stmt = $pdo->prepare("UPDATE games SET status = 'in_progress' WHERE id = ?");
                $stmt->execute([$game['id']]);

                return [
                    'success' => true,
                    'game_id' => $game['id'],
                    'player_color' => $player_color,
                    'message' => 'Both players have joined. The game is starting!'
                ];
            } else {
                return [
                    'success' => true,
                    'game_id' => $game['id'],
                    'player_color' => $player_color,
                    'message' => 'You are Player 2, waiting for Player 1 to start the game.'
                ];
            }
        } else {
            return ['success' => false, 'message' => 'Invalid game code'];
        }
    } catch (Exception $e) {
        error_log('Error joining game: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Unable to join game'];
    }
}


// Function to update the game board after a move
function update_board($game_id, $row, $col, $player_color) {
    global $pdo;
    $log_file = __DIR__ . '/error_game_log.log';

    try {
        // Validate the move
        if (!validate_move($game_id, $row, $col, $player_color)) {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Invalid move by {$player_color} to ({$row}, {$col}).\n", FILE_APPEND);
            return ['success' => false, 'message' => 'Invalid move'];
        }

        // Update the target cell
        $stmt = $pdo->prepare("UPDATE game_board SET cell_status = ? WHERE game_id = ? AND board_row = ? AND board_column = ?");
        $stmt->execute([$player_color, $game_id, $row, $col]);

        // Flip adjacent opponent pieces
        flip_adjacent_pieces($game_id, $row, $col, $player_color);

        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] {$player_color} moved to ({$row}, {$col}).\n", FILE_APPEND);
        return ['success' => true];
    } catch (Exception $e) {
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Error updating board: " . $e->getMessage() . "\n", FILE_APPEND);
        return ['success' => false, 'message' => 'Unable to update the board'];
    }
}


/**
 * Validate if the move is allowed based on game rules.
 * Rules:
 * - The move must be to an empty cell.
 * - The move must be within 1 or 2 steps (diagonal or orthogonal).
 * - The move must not bypass other pieces unless it’s a clone.
 */
function validate_move($game_id, $target_row, $target_col, $player_color) {
    global $pdo;

    try {
        // Fetch the current board state
        $stmt = $pdo->prepare("SELECT board_row, board_column, cell_status FROM game_board WHERE game_id = ?");
        $stmt->execute([$game_id]);
        $board = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Build a 2D array representation of the board
        $boardState = [];
        foreach ($board as $cell) {
            $boardState[$cell['board_row']][$cell['board_column']] = $cell['cell_status'];
        }

        // Loop through all pieces of the current player
        foreach ($board as $cell) {
            if ($cell['cell_status'] === $player_color) {
                $row = (int)$cell['board_row'];
                $col = (int)$cell['board_column'];

                // Calculate valid moves for this piece (1-2 squares in any direction)
                for ($dr = -2; $dr <= 2; $dr++) {
                    for ($dc = -2; $dc <= 2; $dc++) {
                        if ($dr === 0 && $dc === 0) continue; // Skip the current position
                        $newRow = $row + $dr;
                        $newCol = $col + $dc;

                        // Check if the target cell is within bounds and empty
                        if ($newRow >= 1 && $newRow <= 7 && $newCol >= 1 && $newCol <= 7) {
                            if (isset($boardState[$newRow][$newCol]) && $boardState[$newRow][$newCol] === 'empty') {
                                // If target cell matches the move
                                if ($newRow == $target_row && $newCol == $target_col) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }

        return false; // If no valid moves match the target
    } catch (Exception $e) {
        error_log('Error validating move: ' . $e->getMessage());
        return false;
    }
}


/**
 * Flip adjacent opponent pieces to the current player's color.
 */
function flip_adjacent_pieces($game_id, $row, $col, $player_color) {
    global $pdo;

    $opponent_color = ($player_color === 'red') ? 'blue' : 'red';

    // Define the 8 possible adjacent positions
    $adjacent_positions = [
        [-1, -1], [-1, 0], [-1, 1],
        [0, -1],         [0, 1],
        [1, -1], [1, 0], [1, 1]
    ];

    foreach ($adjacent_positions as $position) {
        $adj_row = $row + $position[0];
        $adj_col = $col + $position[1];

        // Update only valid positions
        $stmt = $pdo->prepare("UPDATE game_board SET cell_status = ? WHERE game_id = ? AND board_row = ? AND board_column = ? AND cell_status = ?");
        $stmt->execute([$player_color, $game_id, $adj_row, $adj_col, $opponent_color]);
    }
}



// Function to get the leaderboard
function get_leaderboard($game_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT player_color, COUNT(*) as score FROM game_board WHERE game_id = ? GROUP BY player_color");
        $stmt->execute([$game_id]);
        $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['success' => true, 'scores' => $scores];
    } catch (Exception $e) {
        error_log('Error fetching leaderboard: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Unable to fetch leaderboard'];
    }
}

// Determine the action to take
$action = isset($_GET['action']) ? $_GET['action'] : '';

header('Content-Type: application/json'); // Set header to return JSON

switch ($action) {
    case 'create_game':
        $result = create_game();
        echo json_encode($result);
        break;

    case 'join_game':
        if (isset($_GET['game_code'], $_GET['player_name'])) {
            $game_code = $_GET['game_code'];
            $player_name = $_GET['player_name'];
            $result = join_game($game_code, $player_name);
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Game code and player name required']);
        }
        break;

    case 'start_game':
        if (isset($_GET['game_id'])) {
            $game_id = $_GET['game_id'];
            $result = start_game($game_id);
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Game ID required']);
        }
        break;

    case 'update_board':
        if (isset($_POST['game_id'], $_POST['board_row'], $_POST['board_column'], $_POST['player_color'])) {
            $game_id = $_POST['game_id'];
            $row = $_POST['board_row'];
            $col = $_POST['board_column'];
            $player_color = $_POST['player_color'];

            $result = update_board($game_id, $row, $col, $player_color);
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid move']);
        }
        break;

    case 'get_game_state':
        if (isset($_GET['game_id'])) {
            $game_id = $_GET['game_id'];
            $result = get_game_state($game_id);
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Game ID required']);
        }
        break;

    case 'get_leaderboard':
        if (isset($_GET['game_id'])) {
            $game_id = $_GET['game_id'];
            $result = get_leaderboard($game_id);
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Game ID required']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
