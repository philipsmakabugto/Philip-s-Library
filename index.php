<?php
// -----------------------------
// Part I: Recursive Library Data
// -----------------------------
$library = [
    "Adventure" => [
        "Classics" => [
            "The Odyssey",
            "Journey to the Centre of the Earth",
            "The Lost World"
        ],
        "Modern Adventure" => [
            "Into the Wild",
            "The Alchemist",
            "Tracks"
        ]
    ],
    "Exploration" => [
        "Historical" => [
            "The Lost City of Z",
            "Kon-Tiki",
            "Seven Years in Tibet"
        ],
        "Expeditions" => [
            "Endurance",
            "Into Thin Air",
            "The River of Doubt"
        ]
    ],
    "Travel & Memoir" => [
        "In Patagonia",
        "The Right Stuff"
    ]
];

// -----------------------------
// Part II: Hash Table (Associative Arrays)
// -----------------------------
$bookInfo = [
    // Classics / Fiction
    "The Odyssey" => ["author" => "Homer", "year" => "~8th century BC", "genre" => "Classic Adventure", "covers" => "odyssey_placeholder.jpg"],
    "Journey to the Centre of the Earth" => ["author" => "Jules Verne", "year" => 1864, "genre" => "Science Adventure", "cover" => "centre.jpg"],
    "The Lost World" => ["author" => "Arthur Conan Doyle", "year" => 1912, "genre" => "Adventure", "covers" => "lostworldz.jpg"],

    // Modern Adventure / Fiction
    "Into the Wild" => ["author" => "Jon Krakauer", "year" => 1996, "genre" => "True Adventure", "cover" => "wild.jpg"],
    "The Alchemist" => ["author" => "Paulo Coelho", "year" => 1988, "genre" => "Philosophical Adventure", "cover" => "alchemist.jpg"],
    "Tracks" => ["author" => "Robyn Davidson", "year" => 1980, "genre" => "Travel Memoir", "covers" => "tracks_placeholder.jpg"],

    // Exploration / Historical
    "The Lost City of Z" => ["author" => "David Grann", "year" => 2009, "genre" => "Exploration History", "cover" => "lostz.jpg"],
    "Kon-Tiki" => ["author" => "Thor Heyerdahl", "year" => 1948, "genre" => "Expedition", "cover" => "kontiki.jpg"],
    "Seven Years in Tibet" => ["author" => "Heinrich Harrer", "year" => 1953, "genre" => "Memoir / Exploration", "cover" => "tibet.jpg"],

    // Expeditions / Survival
    "Endurance" => ["author" => "Alfred Lansing", "year" => 1959, "genre" => "Survival Expedition", "cover" => "endurance.jpg"],
    "Into Thin Air" => ["author" => "Jon Krakauer", "year" => 1997, "genre" => "Mountaineering", "cover" => "thin.jpg"],
    "The River of Doubt" => ["author" => "Candice Millard", "year" => 2005, "genre" => "Exploration History", "covers" => "river_of_doubt_placeholder.jpg"],

    // Travel & Memoir
    "In Patagonia" => ["author" => "Bruce Chatwin", "year" => 1977, "genre" => "Travel Memoir", "cover" => "patagonia.jpg"],
    "The Right Stuff" => ["author" => "Tom Wolfe", "year" => 1979, "genre" => "Aviation / Exploration", "covers" => "right_stuff_placeholder.jpg"]
];

function getBookInfo($title, $bookInfo) {
    return $bookInfo[$title] ?? null;
}

// -----------------------------
// Part III: Binary Search Tree
// -----------------------------
class Node {
    public $data;
    public $left;
    public $right;
    public function __construct($data) {
        $this->data = $data;
        $this->left = $this->right = null;
    }
}
class BST {
    public $root = null;
    function insert($data) { $this->root = $this->_insert($this->root, $data); }
    private function _insert($n, $d) {
        if (!$n) return new Node($d);
        // case-insensitive comparison
        if (strcasecmp($d, $n->data) < 0) $n->left = $this->_insert($n->left, $d);
        elseif (strcasecmp($d, $n->data) > 0) $n->right = $this->_insert($n->right, $d);
        return $n;
    }
    function search($data) { return $this->_search($this->root, $data); }
    private function _search($n, $d) {
        if (!$n) return false;
        $cmp = strcasecmp($d, $n->data);
        if ($cmp == 0) return true;
        return $cmp < 0 ? $this->_search($n->left, $d) : $this->_search($n->right, $d);
    }
    function inorder(&$res, $n) {
        if (!$n) return;
        $this->inorder($res, $n->left);
        $res[] = $n->data;
        $this->inorder($res, $n->right);
    }
}

// -----------------------------
// Utility: collect all titles recursively
// -----------------------------
function collectTitles($lib) {
    $titles = [];
    foreach ($lib as $k => $v) {
        if (is_array($v)) {
            // if the array contains only strings -> list of books
            $isBookList = true;
            foreach ($v as $sub) if (is_array($sub)) { $isBookList = false; break; }
            if ($isBookList) {
                foreach ($v as $book) $titles[] = $book;
            } else {
                // deeper categories
                $titles = array_merge($titles, collectTitles($v));
            }
        } else {
            // not expected in this structure, but handle anyway
            $titles[] = $v;
        }
    }
    return $titles;
}

// Build list and BST
$allTitles = collectTitles($library);
$bst = new BST();
foreach ($allTitles as $t) $bst->insert($t);
$alpha = [];
$bst->inorder($alpha, $bst->root);

// -----------------------------
// Simple routing: ?page=home|about|books|contacts
// -----------------------------
$page = $_GET['page'] ?? 'home';

// -----------------------------
// Handle search (simple GET form on home)
// -----------------------------
$searchQuery = trim($_GET['q'] ?? '');
$searchResults = [];
$searchExact = false;
if ($searchQuery !== '') {
    // exact title via BST (case-insensitive)
    $searchExact = $bst->search($searchQuery);
    // partial matches (case-insensitive substring)
    foreach ($allTitles as $t) {
        if (stripos($t, $searchQuery) !== false) $searchResults[] = $t;
    }
}

// -----------------------------
// Handle contact form POST
// -----------------------------
$contactMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($page === 'contacts' || ($_POST['action'] ?? '') === 'contact')) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name === '' || $email === '' || $message === '') {
        $contactMsg = "Please fill all fields.";
    } else {
        $line = "[" . date("Y-m-d H:i:s") . "] Name: $name | Email: $email | Message: " . str_replace(["\r","\n"], ['',' '], $message) . PHP_EOL;
        // append to contacts.txt (ensure writable)
        @file_put_contents(__DIR__ . '/contacts.txt', $line, FILE_APPEND | LOCK_EX);
        $contactMsg = "Thanks $name! Your message has been received.";
    }
}

// -----------------------------
// HTML / UI Output (Cozy Wooden Theme)
// -----------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Philip's Library</title>
<style>
/* ---------------- Cozy Wooden Theme ------------- */
:root{
  --wood-dark: #3b2b22;
  --wood-mid: #5b432f;
  --paper: #f9f6f1;
  --glow: rgba(255,220,120,0.08);
  --accent: #e0b25a;
  --muted: #9a8b7f;
  --card: #fff8f2;
  --text-dark: #2b2622;
  --shadow: rgba(0,0,0,0.35);
}
*{box-sizing:border-box}
html,body{height:100%}
body{
  margin:0;
  font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  background: linear-gradient(180deg,#32261f 0%, #2a2119 60%);
  color:var(--paper);
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
  line-height:1.5;
}

/* Container of site (gives a wooden panel effect) */
.site {
  max-width:1200px;
  margin:28px auto;
  padding:18px;
  background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
  border-radius:12px;
  box-shadow: 0 12px 60px rgba(0,0,0,0.5), 0 2px 0 rgba(255,255,255,0.02) inset;
  border: 1px solid rgba(255,255,255,0.02);
}

/* Header (logo + nav) */
.header {
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding-bottom:14px;
  border-bottom:1px solid rgba(255,255,255,0.03);
}
.brand {
  display:flex;
  align-items:center;
  gap:12px;
}
.logo-badge{
  width:56px;height:56px;border-radius:8px;
  background: linear-gradient(135deg, rgba(224,178,90,0.95), rgba(150,100,50,0.9));
  display:flex;align-items:center;justify-content:center;
  font-weight:900;color:#2b1911;font-size:20px;box-shadow:0 6px 18px rgba(0,0,0,0.5);
}
.brand-title{ font-size:20px; font-weight:800; color:var(--paper); }
.brand-sub{ font-size:12px; color:var(--muted); }

/* Nav */
.nav a{
  color:var(--paper);
  text-decoration:none;
  margin-left:12px;
  padding:8px 10px;
  border-radius:8px;
  font-weight:600;
  transition: all .12s ease;
  background:transparent;
}
.nav a:hover{ background: rgba(255,255,255,0.03); transform: translateY(-2px) }
.nav a.active{ background: rgba(224,178,90,0.12); color:var(--paper); box-shadow: 0 6px 20px rgba(0,0,0,0.45) }

/* Hero panel: wooden shelf feel */
.hero {
  margin-top:18px;
  background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(0,0,0,0.06));
  padding:28px;
  border-radius:10px;
  display:flex;
  gap:20px;
  align-items:center;
  box-shadow: 0 14px 40px rgba(0,0,0,0.5);
  border: 1px solid rgba(255,255,255,0.02);
}
.hero-left{ flex:1; color:var(--paper) }
.hero h1{ margin:0;font-size:28px; color:var(--accent); text-shadow: 0 2px 8px rgba(0,0,0,0.6) }
.hero p{ margin:6px 0 0;color:var(--muted) }

/* Large centered search (cozy card) */
.search-card{
  background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(20,12,8,0.18));
  padding:12px;
  border-radius:10px;
  display:flex;
  gap:8px;
  align-items:center;
  width:480px;
  border:1px solid rgba(224,178,90,0.06);
  box-shadow: 0 8px 30px rgba(0,0,0,0.5);
}
.search-card input{
  flex:1;
  background:transparent;
  border:none;
  outline:none;
  color:var(--paper);
  padding:10px;
  font-size:15px;
}
.search-card button{
  background: linear-gradient(180deg, var(--accent), #c6903a);
  color:#2b1911;
  border:none;
  padding:10px 14px;
  border-radius:8px;
  font-weight:800;
  cursor:pointer;
  box-shadow: 0 6px 18px rgba(0,0,0,0.6);
}

/* Layout below hero */
.content {
  display:flex;
  gap:22px;
  margin-top:20px;
  align-items:flex-start;
}

/* Left main area (books) */
.main {
  flex:1;
}

/* Right sidebar (categories) */
.sidebar {
  width:260px;
  background: linear-gradient(180deg, rgba(255,255,255,0.01), rgba(0,0,0,0.06));
  border-radius:10px;
  padding:12px;
  border:1px solid rgba(255,255,255,0.02);
  box-shadow: 0 8px 30px rgba(0,0,0,0.45);
}

/* Grid cards */
.grid {
  display:grid;
  grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
  gap:18px;
}
.card {
  background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(0,0,0,0.08));
  border-radius:10px;
  padding:10px;
  text-align:center;
  box-shadow: 0 8px 30px rgba(0,0,0,0.45);
  border:1px solid rgba(255,255,255,0.02);
  transition: transform .14s ease, box-shadow .14s ease;
}
.card:hover{ transform: translateY(-6px); box-shadow: 0 18px 60px rgba(0,0,0,0.6) }
.cover img{ width:100%; height:200px; object-fit:cover; border-radius:8px; display:block; }
.title{ margin-top:10px; font-size:14px; font-weight:800; color:var(--accent) }
.author{ font-size:13px; color:var(--muted); margin-top:6px }

/* Sidebar list */
.sidebar strong{ display:block; margin-bottom:8px; color:var(--accent) }
.cat{ font-weight:800; color:var(--paper); margin-top:10px }
.book{ margin-left:8px; margin-top:6px; color:var(--paper) }
.book a{ color:var(--paper); text-decoration:none; font-weight:600 }
.book a:hover{ color:#ffd89a }

/* Contact / About panels */
.panel {
  background: linear-gradient(180deg, rgba(255,255,255,0.01), rgba(0,0,0,0.04));
  padding:14px; border-radius:10px; margin-top:12px;
}

/* Footer */
.footer { margin-top:22px; text-align:center; color:var(--muted); font-size:13px }

/* Modal */
.modal{ display:none; position:fixed; inset:0; background: rgba(0,0,0,0.6); align-items:center; justify-content:center; z-index:1200; padding:20px; }
.modal-content{
  background: linear-gradient(180deg, #fff8f2, #fff6ee);
  border-radius:12px;
  padding:18px;
  width:100%;
  max-width:520px;
  box-shadow: 0 24px 60px rgba(0,0,0,0.6);
  position:relative;
  text-align:center;
  color:var(--text-dark);
}
.modal-content img{ width:220px; height:auto; border-radius:8px; display:block; margin:10px auto; border:1px solid rgba(0,0,0,0.06) }
.close{ position:absolute; right:12px; top:8px; font-size:22px; cursor:pointer; color:var(--text-dark) }

/* Responsive */
@media (max-width:980px){
  .content{ flex-direction:column; }
  .sidebar{ width:100%; order:2; }
  .main{ order:1; }
  .hero{ flex-direction:column; gap:12px; text-align:left; }
  .search-card{ width:100%; }
  .grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); }
}
</style>
</head>
<body>

<div class="site" role="document">
  <header class="header" role="banner">
    <div class="brand">
      <div class="logo-badge">P</div>
      <div>
        <div class="brand-title">Philip's Library</div>
      </div>
    </div>

    <nav class="nav" role="navigation" aria-label="Main navigation">
      <a href="?page=home" class="<?php echo $page==='home'?'active':''; ?>">Home</a>
      <a href="?page=about" class="<?php echo $page==='about'?'active':''; ?>">About</a>
      <a href="?page=books" class="<?php echo $page==='books'?'active':''; ?>">Books</a>
      <a href="?page=contacts" class="<?php echo $page==='contacts'?'active':''; ?>">Contact</a>
    </nav>
  </header>

  <!-- Hero -->
  <section class="hero" aria-label="Hero">
    <div class="hero-left">
      <h1>Philip's Library</h1>
    </div>

    <form class="search-card" method="get" action="" role="search" aria-label="Search books">
      <input type="hidden" name="page" value="home">
      <input type="text" name="q" placeholder="Search books by title" value="<?php echo htmlspecialchars($searchQuery); ?>" aria-label="Search books by title">
      <button type="submit" aria-label="Search">Search</button>
    </form>
  </section>

  <div class="content">
    <main class="main" role="main">
      <?php if ($page === 'home'): ?>

        <div style="margin-top:6px; color:var(--muted)">Featured picks</div>
        <div class="grid" style="margin-top:10px">
          <?php foreach (array_slice($alpha, 0, 9) as $t):
            $info = getBookInfo($t, $bookInfo);
            $img = "covers/" . ($info["cover"] ?? "default.jpg");
          ?>
          <article class="card" role="article">
            <div class="cover"><img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($t); ?> cover"></div>
            <div class="title"><a href="#" class="open-modal" data-book="<?php echo htmlspecialchars($t); ?>"><?php echo htmlspecialchars($t); ?></a></div>
            <div class="author"><?php echo htmlspecialchars($info["author"] ?? "Unknown"); ?></div>
          </article>
          <?php endforeach; ?>
        </div>

        <?php if ($searchQuery !== ''): ?>
          <div style="margin-top:18px">
            <strong style="color:var(--paper)">Search results for "<?php echo htmlspecialchars($searchQuery); ?>"</strong>
            <div class="small" style="margin-top:6px; color:var(--muted)">
                Exact title found via BST: <?php echo $searchExact ? '<strong style="color:#ffdca3">Yes</strong>' : '<strong style="color:#ffdca3">No</strong>'; ?>
            </div>
            <?php if (count($searchResults) === 0): ?>
                <div class="small" style="margin-top:8px; color:var(--muted)">No matches found.</div>
            <?php else: ?>
                <div class="grid" style="margin-top:12px">
                    <?php foreach ($searchResults as $t):
                        $info = getBookInfo($t, $bookInfo);
                        $img = "covers/" . ($info["cover"] ?? "default.jpg");
                    ?>
                    <article class="card">
                        <div class="cover"><img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($t); ?> cover"></div>
                        <div class="title"><a href="#" class="open-modal" data-book="<?php echo htmlspecialchars($t); ?>"><?php echo htmlspecialchars($t); ?></a></div>
                        <div class="author"><?php echo htmlspecialchars($info["author"] ?? "Unknown"); ?></div>
                    </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      <?php elseif ($page === 'about'): ?>
        <div class="panel">
          <h2 style="margin-top:0;color:var(--accent)">About Philip's Library</h2>
          <p style="color:var(--muted)">A warm, curated collection focused on stories of journey, exploration, and survival. This demo showcases recursion for the category tree, a hash table for book metadata, and a binary search tree for title lookups — wrapped in a cozy visual theme.</p>
        </div>

      <?php elseif ($page === 'books'): ?>
        <div>
          <h2 style="color:var(--accent)">All Books (<?php echo count($allTitles); ?>)</h2>
          <div class="grid" style="margin-top:12px">
            <?php foreach ($alpha as $t):
              $info = getBookInfo($t, $bookInfo);
              $img = "covers/" . ($info["cover"] ?? "default.jpg");
            ?>
            <article class="card">
              <div class="cover"><img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($t); ?> cover"></div>
              <div class="title"><a href="#" class="open-modal" data-book="<?php echo htmlspecialchars($t); ?>"><?php echo htmlspecialchars($t); ?></a></div>
              <div class="author"><?php echo htmlspecialchars($info["author"] ?? "Unknown"); ?></div>
            </article>
            <?php endforeach; ?>
          </div>
        </div>

      <?php elseif ($page === 'contacts'): ?>
        <div class="panel">
          <h2 style="margin-top:0;color:var(--accent)">Contact</h2>
          <p style="color:var(--muted)">Have a question or suggestion? Send us a message and we'll get back to you.</p>

          <?php if ($contactMsg !== ''): ?>
              <div style="margin-top:12px;padding:10px;background:rgba(255,255,255,0.03);border-radius:8px;color:var(--paper)"><?php echo htmlspecialchars($contactMsg); ?></div>
          <?php endif; ?>

          <form method="post" action="?page=contacts" style="margin-top:12px">
            <input type="hidden" name="action" value="contact">
            <div style="margin-bottom:8px"><input type="text" name="name" placeholder="Your name" style="width:100%;padding:10px;border-radius:6px;border:none;background:rgba(255,255,255,0.02);color:var(--paper)" required></div>
            <div style="margin-bottom:8px"><input type="email" name="email" placeholder="Email" style="width:100%;padding:10px;border-radius:6px;border:none;background:rgba(255,255,255,0.02);color:var(--paper)" required></div>
            <div style="margin-bottom:8px"><textarea name="message" placeholder="Message" rows="5" style="width:100%;padding:10px;border-radius:6px;border:none;background:rgba(255,255,255,0.02);color:var(--paper)" required></textarea></div>
            <div><button type="submit" style="padding:10px 14px;background:linear-gradient(180deg,var(--accent),#c6903a);border:none;color:#2b1911;border-radius:8px;cursor:pointer">Send</button></div>
          </form>
        </div>

      <?php else: ?>
        <div class="panel"><h2>Page not found</h2></div>
      <?php endif; ?>
    </main>

    <aside class="sidebar" role="complementary">
      <strong>Categories</strong>
      <?php
      // define displayLibrary if not exists (safe for multiple includes)
      if (!function_exists('displayLibrary')) {
          function displayLibrary($library, $indent = 0) {
              foreach ($library as $key => $value) {
                  if (is_array($value)) {
                      $isBookList = true;
                      foreach ($value as $sub) if (is_array($sub)) { $isBookList = false; break; }
                      echo "<div class='cat' style='margin-top:10px;padding-left:".($indent*6)."px'>" . htmlspecialchars($key) . "</div>";
                      if ($isBookList) {
                          foreach ($value as $book) {
                              echo "<div class='book' style='padding-left:".(($indent+1)*8)."px'><a href='#' class='open-modal' data-book='" . htmlspecialchars($book) . "'>" . htmlspecialchars($book) . "</a></div>";
                          }
                      } else {
                          displayLibrary($value, $indent + 1);
                      }
                  }
              }
          }
      }
      displayLibrary($library);
      ?>
    </aside>
  </div>

  <div class="footer">&copy; <?php echo date('Y'); ?> Philip's Library. All rights reserved.</div>
</div>

<!-- Modal -->
<div class="modal" id="bookModal" aria-hidden="true">
  <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="mTitle">
    <span class="close" aria-label="Close">&times;</span>
    <h3 id="mTitle"></h3>
    <img id="mCover" src="" alt="">
    <p id="mAuthor"></p>
    <p id="mYear"></p>
    <p id="mGenre"></p>
  </div>
</div>

<script>
// pass PHP bookData to JS
const bookData = <?php echo json_encode($bookInfo, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;

const modal = document.getElementById('bookModal');
const mTitle = document.getElementById('mTitle');
const mCover = document.getElementById('mCover');
const mAuthor = document.getElementById('mAuthor');
const mYear = document.getElementById('mYear');
const mGenre = document.getElementById('mGenre');

document.querySelectorAll('.open-modal').forEach(a=>{
  a.addEventListener('click', e=>{
    e.preventDefault();
    const book = a.dataset.book;
    const info = bookData[book];
    mTitle.textContent = book;
    if(info){
      mCover.src = 'covers/' + (info.cover || 'default.jpg');
      mAuthor.textContent = "Author: " + (info.author || 'Unknown');
      mYear.textContent = "Year: " + (info.year || '');
      mGenre.textContent = "Genre: " + (info.genre || '');
    } else {
      mCover.src = '';
      mAuthor.textContent = 'No info';
      mYear.textContent = '';
      mGenre.textContent = '';
    }
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden','false');
  });
});
document.querySelector('.close').onclick = ()=> { modal.style.display='none'; modal.setAttribute('aria-hidden','true'); };
window.onclick = e => { if (e.target==modal) { modal.style.display='none'; modal.setAttribute('aria-hidden','true'); } }
</script>
</body>
</html>
