\# Οδηγός Χρήσης και Τεκμηρίωση API για Παιχνίδι Στρατηγικής 7x7
 
\## Περιγραφή Παιχνιδιού
 
Το παιχνίδι στρατηγικής 7x7 είναι ένα διαδραστικό παιχνίδι δύο παικτών που βασίζεται σε έναν πίνακα 7x7.
 
Σκοπός του παιχνιδιού είναι οι παίκτες να καταλάβουν όσο το δυνατόν περισσότερες κυψέλες στον πίνακα, προκειμένου
 
να αποκτήσουν τον έλεγχο του μεγαλύτερου μέρους του. Κάθε παίκτης ξεκινά από μια προκαθορισμένη γωνιακή θέση και
 
προχωρά επιλέγοντας γειτονικές κυψέλες για να τις καταλάβει. Οι κανόνες του παιχνιδιού περιλαμβάνουν εναλλαγή σειρών
 
και δυνατότητα κατάληψης κυψελών του αντιπάλου, εφόσον πληρούνται οι κατάλληλες προϋποθέσεις.
 
Το παιχνίδι υποστηρίζει:
 
\- Δημιουργία νέου παιχνιδιού με μοναδικό κωδικό.
 
\- Συμμετοχή δεύτερου παίκτη μέσω του κωδικού αυτού.
 
\- Δυναμική ενημέρωση του πίνακα σε πραγματικό χρόνο.
 
\- Προβολή του σκορ των παικτών μέσα από το leaderboard.
 
Η υλοποίηση περιλαμβάνει backend σε PHP, δυναμική διαχείριση του UI μέσω JavaScript, και αποθήκευση δεδομένων σε MySQL.
 
URL: [http://petly.gr/index.html](http://petly.gr/index.html) 
 
\---
 
\## Εγκατάσταση
 
\### Απαιτήσεις
 
\- PHP >= 7.4
 
\- MySQL >= 5.7
 
\- Apache ή Nginx
 
\- Πρόγραμμα περιήγησης για διαδραστικότητα
 
\### Βήματα Εγκατάστασης
 
1\. **Ρύθμιση Βάσης Δεδομένων:** Δημιουργήστε τους απαραίτητους πίνακες στη MySQL.
 
2\. **Διαμόρφωση Συνδέσεων:** Ενημερώστε το αρχείο `db.php` με τις ρυθμίσεις σύνδεσης στη βάση δεδομένων.
 
3\. **Ανέβασμα Αρχείων:** Ανεβάστε όλα τα αρχεία στον εξυπηρετητή.
 
4\. **Πρόσβαση:** Ανοίξτε το `index.html` ή το βασικό URL της εφαρμογής.
 
\---
 
\## Περιγραφή API
 
Το API παρέχει λειτουργίες για τη διαχείριση του παιχνιδιού. Παρακάτω παρατίθεται αναλυτική τεκμηρίωση:
 
\### 1. Δημιουργία Νέου Παιχνιδιού
 
**Endpoint:** `/game.php?action=create_game`
 
**Μέθοδος:** `GET`
 
**Περιγραφή:** Δημιουργεί ένα νέο παιχνίδι και επιστρέφει έναν μοναδικό κωδικό παιχνιδιού.
 
**Ανάλυση Απόκρισης:**
 
\- `success` (boolean): Επιστρέφει `true` αν η δημιουργία ήταν επιτυχής.
 
\- `game_id` (int): Το μοναδικό ID του παιχνιδιού.
 
\- `game_code` (string): Ο μοναδικός κωδικός του παιχνιδιού.
 
\- `message` (string): Μήνυμα κατάστασης.
 
**Παράδειγμα Απόκρισης:**
 
\`\`\`json
 
{
 
"success": true,
 
"game\_id": 1,
 
"game\_code": "ABC123",
 
"message": "You are the first player. Waiting for the second player."
 
}
 
\`\`\`
 
\---
 
\### 2. Συμμετοχή σε Παιχνίδι
 
**Endpoint:** `/game.php?action=join_game`
 
**Μέθοδος:** `GET`
 
**Περιγραφή:** Επιτρέπει σε έναν δεύτερο παίκτη να συμμετάσχει σε ένα παιχνίδι χρησιμοποιώντας έναν μοναδικό κωδικό παιχνιδιού.
 
**Παράμετροι:**
 
\- `game_code` (string): Ο μοναδικός κωδικός του παιχνιδιού.
 
\- `player_name` (string): Το όνομα του παίκτη.
 
**Ανάλυση Απόκρισης:**
 
\- `success` (boolean): Επιστρέφει `true` αν η συμμετοχή ήταν επιτυχής.
 
\- `game_id` (int): Το μοναδικό ID του παιχνιδιού.
 
\- `player_color` (string): Το χρώμα που αντιστοιχεί στον παίκτη (π.χ., `red` ή `blue`).
 
\- `message` (string): Μήνυμα κατάστασης.
 
**Παράδειγμα Απόκρισης:**
 
\`\`\`json
 
{
 
"success": true,
 
"game\_id": 1,
 
"player\_color": "blue",
 
"message": "Both players have joined. The game is starting!"
 
}
 
\`\`\`
 
\---
 
\### 3. Εκκίνηση Παιχνιδιού
 
**Endpoint:** `/game.php?action=start_game`
 
**Μέθοδος:** `GET`
 
**Περιγραφή:** Ορίζει την κατάσταση του παιχνιδιού σε `in_progress`, υποδεικνύοντας ότι το παιχνίδι έχει ξεκινήσει.
 
**Παράμετροι:**
 
\- `game_id` (int): Το μοναδικό ID του παιχνιδιού.
 
**Ανάλυση Απόκρισης:**
 
\- `success` (boolean): Επιστρέφει `true` αν η αλλαγή κατάστασης ήταν επιτυχής.
 
\- `message` (string): Μήνυμα κατάστασης.
 
**Παράδειγμα Απόκρισης:**
 
\`\`\`json
 
{
 
"success": true,
 
"message": "Game has started!"
 
}
 
\`\`\`
 
\---
 
\### 4. Ενημέρωση Πίνακα
 
**Endpoint:** `/game.php?action=update_board`
 
**Μέθοδος:** `POST`
 
**Περιγραφή:** Καταγράφει μια κίνηση παίκτη και ενημερώνει τον πίνακα παιχνιδιού.
 
**Παράμετροι:**
 
\- `game_id` (int): Το μοναδικό ID του παιχνιδιού.
 
\- `board_row` (int): Η γραμμή της κίνησης (0-6).
 
\- `board_column` (int): Η στήλη της κίνησης (0-6).
 
\- `player_color` (string): Το χρώμα του παίκτη που εκτελεί την κίνηση.
 
**Ανάλυση Απόκρισης:**
 
\- `success` (boolean): Επιστρέφει `true` αν η κίνηση καταχωρήθηκε επιτυχώς.
 
\- `message` (string, optional): Περιγραφή λάθους σε περίπτωση αποτυχίας.
 
**Παράδειγμα Απόκρισης:**
 
\`\`\`json
 
{
 
"success": true
 
}
 
\`\`\`
 
\---
 
\### 5. Ανάκτηση Κατάστασης Παιχνιδιού
 
**Endpoint:** `/game.php?action=get_game_state`
 
**Μέθοδος:** `GET`
 
**Περιγραφή:** Επιστρέφει την τρέχουσα κατάσταση του παιχνιδιού, συμπεριλαμβανομένων των παικτών, του πίνακα και της κατάστασης του παιχνιδιού.
 
**Παράμετροι:**
 
\- `game_id` (int): Το μοναδικό ID του παιχνιδιού.
 
**Ανάλυση Απόκρισης:**
 
\- `success` (boolean): Επιστρέφει `true` αν η ανάκτηση ήταν επιτυχής.
 
\- `status` (string): Η κατάσταση του παιχνιδιού (\`waiting\`, `in_progress`, ή `finished`).
 
\- `players` (array): Λίστα με τους παίκτες και τα χαρακτηριστικά τους.
 
\- `board` (object): Πληροφορίες για τον πίνακα (διαστάσεις και κατάσταση κυψελών).
 
**Παράδειγμα Απόκρισης:**
 
\`\`\`json
 
{
 
"success": true,
 
"status": "in\_progress",
 
"players": \[
 
{"player\_name": "Player1", "player\_color": "red"},
 
{"player\_name": "Player2", "player\_color": "blue"}
 
\],
 
"board": {
 
"rows": 7,
 
"columns": 7,
 
"cell\_status": "\[...\]
 
}
 
}
 
\`\`\`
 
\---
 
\### 6. Προβολή Leaderboard
 
**Endpoint:** `/game.php?action=get_leaderboard`
 
**Μέθοδος:** `GET`
 
**Περιγραφή:** Παρέχει τα τρέχοντα σκορ των παικτών στο παιχνίδι.
 
**Παράμετροι:**
 
\- `game_id` (int): Το μοναδικό ID του παιχνιδιού.
 
**Ανάλυση Απόκρισης:**
 
\- `success` (boolean): Επιστρέφει `true` αν τα σκορ ανακτήθηκαν επιτυχώς.
 
\- `scores` (array): Λίστα με τα σκορ των παικτών.
 
**Παράδειγμα Απόκρισης:**
 
\`\`\`json
 
{
 
"success": true,
 
"scores": \[
 
{"player\_color": "red", "score": 10},
 
{"player\_color": "blue", "score": 8}
 
\]
 
}
 
\`\`\`
 
\---
 
\## Τεχνολογίες
 
\- **Backend:** PHP
 
\- **Frontend:** JavaScript (DOM Manipulation, Fetch API)
 
\- **Database:** MySQL
 
\------------------------------------------------------------------------------------------------------------------------------------
 
Περιγραφή της Βάσης Δεδομένων:
 
Είναι φτιαγμένη στο πρόγραμμα phpMyAdmin.
 
Όνομα Βάσης: ataax\_game
 
Πίνακες:
 
\--games
 
id............: Int \\\\Αρίθμηση
 
game\_code ....: Varchar. \\\\Ο μοναδικός κωδικός αναγνώρισης του παιxνιδιού.
 
status........: enum('waiting','in\_progress','completed') \\\\Κατάσταση του παιχνιδιού
 
created\_at....: TimeStamp \\\\Ημερομηνία & ώρα δημιουργίας του παιχνιδιού
 
player\_count..: Int \\\\Πόσοι παίχτες συνδέθηκαν στο παιχνίδι
 

\--game\_board
 
id............: Int \\\\Αρίθμηση
 
game\_id.......: Int \\\\Κωδικός του παιχνιδιού
 
doard\_row.....: Int \\\\Αριθμός γραμμής
 
board\_column..: Int \\\\Αριθμός στήλης
 
cell\_status...: enum('empty','red','blue') \\\\Κατάσταση κελιού
 
\--players
 
id............: Int \\\\Αρίθμηση
 
game\_id.......: Int \\\\Κωδικός του παιχνιδιού
 
player\_name...: Varchar(50) \\\\Όνομα παίκτη
 
player\_color..: enum('red','blue') \\\\Χρώμα παίκτη
 
last\_active...: TimeStamp \\\\Τελευταία κίνηση
 
\--scores
 
id............: Int \\\\Αρίθμηση
 
game\_id.......: Int \\\\Κωδικός παιχνιδιού
 
player\_color..: enum('red','blue') \\\\Χρώμα παίκτη
 
score.........: Int \\\\Σκορ
 
\------------------------------------------------------------------------------------------------------------------------------------
 
\## Συγγραφέας
 
Το project αναπτύχθηκε από Φώτης Αλεξίου, Βασίλης Παναγιώτης Κεχαγιάς.
 
Για απορίες ή αναφορές σφαλμάτων, παρακαλώ επικοινωνήστε μέσω [billis.kehagias@gmail.com](mailto:billis.kehagias@gmail.com)  / [fotisalexiou002@gmail.com](mailto:fotisalexiou002@gmail.com)  .