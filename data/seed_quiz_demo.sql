-- =====================================================
-- Seed Quiz/Formations : 5 cours complets pour demo
-- =====================================================
-- Supprime les anciennes donnees demo + insere du contenu pedagogique reel
-- 5 cours : HTML, CSS, JavaScript, PHP, Python
-- Chaque cours : 3-5 lecons publiees + 1 quiz de 4-5 questions + reponses
-- Auteurs : encadrants existants (Nabil id=11, Amira id=12, Ines id=14, Hichem id=15)
-- 2 certificats demo pour tester l'affichage
-- =====================================================

USE edumatch;

-- Nettoyage (ON DELETE CASCADE supprime tout en chaine via courses)
DELETE FROM certificates;
DELETE FROM courses;

-- Reset auto-increment pour avoir des IDs propres
ALTER TABLE certificates AUTO_INCREMENT = 1;
ALTER TABLE responses AUTO_INCREMENT = 1;
ALTER TABLE questions AUTO_INCREMENT = 1;
ALTER TABLE lessons AUTO_INCREMENT = 1;
ALTER TABLE quizzes AUTO_INCREMENT = 1;
ALTER TABLE courses AUTO_INCREMENT = 1;

-- =====================================================
-- COURS 1 : HTML5 Fondamentaux (Nabil Gharbi)
-- =====================================================
INSERT INTO courses (user_id, title, description, level, status) VALUES
(11, 'HTML5 Fondamentaux',
 'Maitrisez les bases du HTML5 pour creer des pages web modernes et accessibles. Apprenez la structure semantique, les formulaires avances, l''integration multimedia et les bonnes pratiques SEO. Parfait pour les debutants qui souhaitent demarrer leur parcours en developpement web.',
 'beginner', 'published');
SET @course_html = LAST_INSERT_ID();

INSERT INTO lessons (course_id, title, summary, content, duration_minutes, lesson_order, status) VALUES
(@course_html, 'Introduction au HTML5',
 'Decouvrez l''histoire du HTML, son role dans le web moderne et la structure d''un document HTML5.',
 '<h2>Qu''est-ce que le HTML ?</h2><p>HTML signifie <strong>HyperText Markup Language</strong>. C''est le langage standard utilise pour creer des pages web depuis 1993.</p><h3>La structure de base</h3><pre><code>&lt;!DOCTYPE html&gt;\n&lt;html lang="fr"&gt;\n&lt;head&gt;\n  &lt;meta charset="UTF-8"&gt;\n  &lt;title&gt;Ma page&lt;/title&gt;\n&lt;/head&gt;\n&lt;body&gt;\n  &lt;h1&gt;Bonjour&lt;/h1&gt;\n&lt;/body&gt;\n&lt;/html&gt;</code></pre><h3>Les nouveautes HTML5</h3><ul><li>Balises semantiques (header, nav, article, section, footer)</li><li>Support natif audio/video</li><li>Canvas pour graphiques 2D</li><li>API geolocalisation, stockage local, drag & drop</li></ul>',
 15, 1, 'published'),

(@course_html, 'Les balises semantiques',
 'Apprenez a structurer vos pages avec header, nav, main, article, section, aside et footer.',
 '<h2>Pourquoi la semantique ?</h2><p>Les balises semantiques donnent du sens a votre structure HTML. Elles ameliorent l''accessibilite, le referencement SEO et la maintenance du code.</p><h3>Les balises principales</h3><ul><li><strong>&lt;header&gt;</strong> : en-tete de page ou de section</li><li><strong>&lt;nav&gt;</strong> : menu de navigation</li><li><strong>&lt;main&gt;</strong> : contenu principal unique</li><li><strong>&lt;article&gt;</strong> : contenu autonome (post, article)</li><li><strong>&lt;section&gt;</strong> : section thematique</li><li><strong>&lt;aside&gt;</strong> : contenu connexe (sidebar)</li><li><strong>&lt;footer&gt;</strong> : pied de page</li></ul><p>Comparez : &lt;div class="header"&gt; vs &lt;header&gt; — le deuxieme est explicite pour les lecteurs d''ecran et moteurs de recherche.</p>',
 20, 2, 'published'),

(@course_html, 'Formulaires HTML5 avances',
 'Maitrisez les nouveaux types d''input, la validation native et l''accessibilite des formulaires.',
 '<h2>Les nouveaux types d''input</h2><p>HTML5 introduit de nombreux types specialises :</p><ul><li><code>email</code> : validation email automatique</li><li><code>tel</code> : clavier numerique sur mobile</li><li><code>url</code>, <code>number</code>, <code>date</code>, <code>color</code>, <code>range</code></li><li><code>search</code> : champ de recherche avec bouton clear</li></ul><h3>Attributs de validation</h3><pre><code>&lt;input type="email" required minlength="5"&gt;\n&lt;input type="number" min="0" max="100" step="5"&gt;\n&lt;input pattern="[A-Za-z]{3,}" title="3+ lettres"&gt;</code></pre><h3>Accessibilite</h3><p>Toujours associer un <code>&lt;label&gt;</code> a chaque <code>&lt;input&gt;</code> via l''attribut <code>for</code>.</p>',
 25, 3, 'published'),

(@course_html, 'Multimedia : audio et video',
 'Integrez du contenu audio et video sans dependances externes grace aux balises HTML5 natives.',
 '<h2>La balise &lt;video&gt;</h2><pre><code>&lt;video controls width="640" poster="cover.jpg"&gt;\n  &lt;source src="film.mp4" type="video/mp4"&gt;\n  &lt;source src="film.webm" type="video/webm"&gt;\n  Votre navigateur ne supporte pas la video.\n&lt;/video&gt;</code></pre><h3>Attributs utiles</h3><ul><li><code>controls</code> : affiche les controles de lecture</li><li><code>autoplay muted</code> : lecture auto (muet obligatoire sur mobile)</li><li><code>loop</code> : repete en boucle</li><li><code>poster</code> : image de couverture</li></ul><h3>La balise &lt;audio&gt;</h3><p>Meme principe avec <code>&lt;source&gt;</code> pour les formats mp3, ogg, wav.</p>',
 18, 4, 'published'),

(@course_html, 'Accessibilite et SEO',
 'Optimisez vos pages pour les utilisateurs handicapes et les moteurs de recherche.',
 '<h2>Bonnes pratiques accessibilite (a11y)</h2><ul><li>Utiliser <code>alt</code> sur toutes les images : <code>&lt;img src="..." alt="Description"&gt;</code></li><li>Hierarchie correcte des titres h1 → h6 (un seul h1 par page)</li><li>Attributs ARIA pour les composants interactifs : <code>aria-label</code>, <code>role</code></li><li>Contraste de couleurs suffisant (ratio 4.5:1 minimum)</li></ul><h2>SEO de base</h2><pre><code>&lt;meta name="description" content="Resume de la page"&gt;\n&lt;meta property="og:title" content="Titre pour reseaux sociaux"&gt;\n&lt;link rel="canonical" href="https://..."&gt;</code></pre><p>Le HTML semantique est deja un excellent point de depart pour le SEO.</p>',
 22, 5, 'published');

INSERT INTO quizzes (course_id, title, description, duration_minutes, passing_score, max_attempts) VALUES
(@course_html, 'Quiz HTML5 - Validation des acquis',
 'Verifiez vos connaissances sur la structure HTML, les balises semantiques et les formulaires.',
 15, 60.00, 3);
SET @quiz_html = LAST_INSERT_ID();

INSERT INTO questions (quiz_id, question_text, question_type, points, question_order, explanation) VALUES
(@quiz_html, 'Quelle balise HTML5 est utilisee pour le contenu principal unique d''une page ?', 'single_choice', 1.00, 1, 'La balise <main> est destinee au contenu central de la page et ne doit apparaitre qu''une seule fois.'),
(@quiz_html, 'Quel attribut rend un champ de formulaire obligatoire ?', 'single_choice', 1.00, 2, 'L''attribut "required" force la saisie avant soumission. Validation native du navigateur.'),
(@quiz_html, 'Le HTML est un langage de programmation.', 'true_false', 1.00, 3, 'FAUX. Le HTML est un langage de balisage (markup), pas de programmation.'),
(@quiz_html, 'Quelle balise permet d''inclure une video sans plugin externe ?', 'single_choice', 1.00, 4, 'La balise <video> est native HTML5 et accepte plusieurs <source> pour la compatibilite.'),
(@quiz_html, 'Pour l''accessibilite, on doit toujours fournir un attribut "alt" sur :', 'single_choice', 1.00, 5, 'L''attribut alt decrit l''image pour les lecteurs d''ecran et s''affiche si l''image ne charge pas.');

INSERT INTO responses (question_id, response_text, is_correct, response_order) VALUES
-- Q1
(1, '<body>', 0, 1), (1, '<section>', 0, 2), (1, '<main>', 1, 3), (1, '<article>', 0, 4),
-- Q2
(2, 'mandatory', 0, 1), (2, 'required', 1, 2), (2, 'needed', 0, 3), (2, 'must', 0, 4),
-- Q3 vrai/faux
(3, 'Vrai', 0, 1), (3, 'Faux', 1, 2),
-- Q4
(4, '<video>', 1, 1), (4, '<media>', 0, 2), (4, '<movie>', 0, 3), (4, '<player>', 0, 4),
-- Q5
(5, 'Les balises <img>', 1, 1), (5, 'Les balises <p>', 0, 2), (5, 'Les balises <div>', 0, 3), (5, 'Aucune', 0, 4);


-- =====================================================
-- COURS 2 : CSS3 & Design Moderne (Amira Ksouri)
-- =====================================================
INSERT INTO courses (user_id, title, description, level, status) VALUES
(12, 'CSS3 & Design Moderne',
 'Concevez des interfaces web responsives et elegantes avec CSS3. Maitrisez Flexbox, Grid, les animations, les variables CSS et les bonnes pratiques de design system. Niveau intermediaire avec exercices pratiques.',
 'intermediate', 'published');
SET @course_css = LAST_INSERT_ID();

INSERT INTO lessons (course_id, title, summary, content, duration_minutes, lesson_order, status) VALUES
(@course_css, 'Selecteurs CSS et specificite',
 'Comprendre comment cibler precisement les elements et resoudre les conflits de styles.',
 '<h2>Types de selecteurs</h2><ul><li><strong>Element</strong> : <code>p { color: red; }</code></li><li><strong>Classe</strong> : <code>.btn { padding: 10px; }</code></li><li><strong>ID</strong> : <code>#header { height: 80px; }</code></li><li><strong>Attribut</strong> : <code>input[type="email"] { ... }</code></li><li><strong>Pseudo-classe</strong> : <code>a:hover { ... }</code></li><li><strong>Pseudo-element</strong> : <code>p::first-letter { ... }</code></li></ul><h3>La specificite (calcul du poids)</h3><p>Format : <code>(inline, ID, classe, element)</code></p><ul><li><code>p</code> = (0,0,0,1)</li><li><code>.btn</code> = (0,0,1,0)</li><li><code>#nav</code> = (0,1,0,0)</li><li><code>style=""</code> = (1,0,0,0)</li></ul><p>Plus le poids est eleve, plus la regle prime.</p>',
 25, 1, 'published'),

(@course_css, 'Flexbox - Layout flexible',
 'Maitrisez Flexbox pour aligner et distribuer vos elements sur une dimension.',
 '<h2>Configuration du container</h2><pre><code>.container {\n  display: flex;\n  flex-direction: row | column;\n  justify-content: flex-start | center | space-between;\n  align-items: stretch | center | flex-end;\n  gap: 20px;\n}</code></pre><h3>Proprietes des enfants</h3><pre><code>.item {\n  flex: 1; /* grow shrink basis */\n  align-self: center;\n  order: 2;\n}</code></pre><h3>Cas d''usage typiques</h3><ul><li>Navigation horizontale</li><li>Cards centrees</li><li>Footer colle en bas (flex: 1 sur main)</li><li>Holy grail layout</li></ul>',
 30, 2, 'published'),

(@course_css, 'CSS Grid - Layout en 2D',
 'Creez des grilles complexes avec CSS Grid, ideal pour des layouts en 2 dimensions.',
 '<h2>Definir une grille</h2><pre><code>.grid {\n  display: grid;\n  grid-template-columns: 1fr 2fr 1fr;\n  grid-template-rows: 80px auto 60px;\n  gap: 20px;\n}</code></pre><h3>Areas nommees (le plus puissant)</h3><pre><code>.layout {\n  display: grid;\n  grid-template-areas:\n    "header header header"\n    "sidebar main main"\n    "footer footer footer";\n  grid-template-columns: 200px 1fr 1fr;\n}\n.header { grid-area: header; }\n.sidebar { grid-area: sidebar; }</code></pre><h3>Repeat et minmax</h3><p><code>grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));</code> cree automatiquement des colonnes responsives.</p>',
 30, 3, 'published'),

(@course_css, 'Animations et transitions',
 'Ajoutez du mouvement et de la vie a vos interfaces.',
 '<h2>Transitions (simples)</h2><pre><code>.btn {\n  background: blue;\n  transition: all 0.3s ease;\n}\n.btn:hover {\n  background: red;\n  transform: scale(1.05);\n}</code></pre><h3>Animations (sequences)</h3><pre><code>@keyframes slideIn {\n  from { opacity: 0; transform: translateX(-50px); }\n  to   { opacity: 1; transform: translateX(0); }\n}\n.card {\n  animation: slideIn 0.6s ease-out;\n}</code></pre><h3>Performance</h3><p>Pour des animations fluides 60fps, animer uniquement <code>transform</code> et <code>opacity</code>. Eviter <code>top/left/width</code> qui declenchent un reflow.</p>',
 25, 4, 'published'),

(@course_css, 'Variables CSS et theming',
 'Centralisez vos valeurs avec les custom properties et creez des themes dynamiques.',
 '<h2>Definition des variables</h2><pre><code>:root {\n  --primary: #525fe1;\n  --primary-dark: #3b47c9;\n  --text: #0b104a;\n  --radius: 12px;\n  --shadow: 0 4px 12px rgba(0,0,0,0.1);\n}\n.btn {\n  background: var(--primary);\n  border-radius: var(--radius);\n  box-shadow: var(--shadow);\n}</code></pre><h3>Dark mode</h3><pre><code>[data-theme="dark"] {\n  --primary: #6c7cff;\n  --text: #ffffff;\n  --bg: #0a0a0a;\n}</code></pre><p>Toggle via JavaScript : <code>document.documentElement.setAttribute("data-theme", "dark");</code></p>',
 20, 5, 'published');

INSERT INTO quizzes (course_id, title, description, duration_minutes, passing_score, max_attempts) VALUES
(@course_css, 'Quiz CSS3 - Layout & Design', 'Testez votre maitrise de Flexbox, Grid, animations et variables CSS.', 20, 65.00, 2);
SET @quiz_css = LAST_INSERT_ID();

INSERT INTO questions (quiz_id, question_text, question_type, points, question_order, explanation) VALUES
(@quiz_css, 'Quelle propriete Flexbox aligne les elements sur l''axe principal ?', 'single_choice', 1.00, 1, 'justify-content controle l''axe principal (par defaut horizontal). align-items controle l''axe transversal.'),
(@quiz_css, 'CSS Grid est plus adapte que Flexbox pour des layouts en 2 dimensions.', 'true_false', 1.00, 2, 'VRAI. Flexbox est 1D, Grid est 2D (lignes + colonnes).'),
(@quiz_css, 'Comment definir une variable CSS reutilisable ?', 'single_choice', 1.00, 3, 'Les variables CSS (custom properties) sont definies avec --nom et accessibles via var(--nom).'),
(@quiz_css, 'Quelles proprietes CSS animent le plus efficacement (60fps) ?', 'multiple_choice', 1.50, 4, 'transform et opacity sont les seules proprietes accelerees GPU sans declencher de reflow.'),
(@quiz_css, 'Quel selecteur a la plus haute specificite ?', 'single_choice', 1.00, 5, 'Un ID a une specificite de (0,1,0,0), superieure a une classe (0,0,1,0) ou a un element (0,0,0,1).');

INSERT INTO responses (question_id, response_text, is_correct, response_order) VALUES
-- Q1 (id=6)
(6, 'align-items', 0, 1), (6, 'justify-content', 1, 2), (6, 'flex-direction', 0, 3), (6, 'align-content', 0, 4),
-- Q2 (id=7) vrai/faux
(7, 'Vrai', 1, 1), (7, 'Faux', 0, 2),
-- Q3 (id=8)
(8, '@var --primary: red;', 0, 1), (8, '--primary: red;', 1, 2), (8, '$primary: red;', 0, 3), (8, 'var primary = red;', 0, 4),
-- Q4 (id=9) multi
(9, 'transform', 1, 1), (9, 'opacity', 1, 2), (9, 'width', 0, 3), (9, 'top', 0, 4),
-- Q5 (id=10)
(10, '.classe', 0, 1), (10, '#identifiant', 1, 2), (10, 'div p', 0, 3), (10, '*', 0, 4);


-- =====================================================
-- COURS 3 : JavaScript ES6+ (Hichem Abidi)
-- =====================================================
INSERT INTO courses (user_id, title, description, level, status) VALUES
(15, 'JavaScript ES6+ Moderne',
 'Plongez dans le JavaScript moderne ES6 et au-dela. Maitrisez les arrow functions, les promises, async/await, les modules, le destructuring et la programmation orientee objet moderne. Pour developpeurs ayant des bases en JS.',
 'advanced', 'published');
SET @course_js = LAST_INSERT_ID();

INSERT INTO lessons (course_id, title, summary, content, duration_minutes, lesson_order, status) VALUES
(@course_js, 'let, const et scope de bloc',
 'Comprendre les nouveautes de declaration de variables introduites en ES6.',
 '<h2>var vs let vs const</h2><pre><code>// var : scope de fonction, hoisting\nvar x = 1;\nif (true) { var x = 2; } // ecrase le precedent\n\n// let : scope de bloc, pas de re-declaration\nlet y = 1;\nif (true) { let y = 2; } // y interne = 2, externe = 1\n\n// const : meme scope que let + valeur immuable\nconst PI = 3.14;\n// PI = 3.15; // TypeError</code></pre><h3>Bonne pratique</h3><p>Utiliser <strong>const par defaut</strong>, <strong>let</strong> si reassignation necessaire, et <strong>jamais var</strong> en code moderne.</p>',
 20, 1, 'published'),

(@course_js, 'Arrow functions et this',
 'Decouvrez la syntaxe concise des arrow functions et leur comportement particulier avec this.',
 '<h2>Syntaxe</h2><pre><code>// Function traditionnelle\nfunction add(a, b) { return a + b; }\n\n// Arrow function\nconst add = (a, b) => a + b;\n\n// Avec un seul parametre, parentheses optionnelles\nconst double = x => x * 2;\n\n// Corps avec multiples instructions\nconst greet = name => {\n  const msg = `Bonjour ${name}`;\n  return msg.toUpperCase();\n};</code></pre><h3>Comportement de this</h3><p>Les arrow functions <strong>ne possedent pas leur propre this</strong>. Elles heritent du this du contexte englobant. Tres utile pour les callbacks et methodes de classe.</p>',
 22, 2, 'published'),

(@course_js, 'Destructuring et spread',
 'Extraire et manipuler facilement des donnees depuis objets et tableaux.',
 '<h2>Destructuring objet</h2><pre><code>const user = { name: "Ali", age: 25, city: "Tunis" };\nconst { name, age } = user;\n// Renommage\nconst { name: userName } = user;\n// Valeur par defaut\nconst { country = "TN" } = user;</code></pre><h3>Destructuring tableau</h3><pre><code>const [first, second, ...rest] = [1, 2, 3, 4, 5];\n// first=1, second=2, rest=[3,4,5]</code></pre><h3>Spread operator</h3><pre><code>const arr1 = [1, 2, 3];\nconst arr2 = [...arr1, 4, 5];\n\nconst obj1 = { a: 1, b: 2 };\nconst obj2 = { ...obj1, c: 3 };</code></pre>',
 25, 3, 'published'),

(@course_js, 'Promises et async/await',
 'Gerez l''asynchrone proprement sans callbacks imbriques.',
 '<h2>Promise basique</h2><pre><code>const fetchUser = (id) => {\n  return new Promise((resolve, reject) => {\n    if (id > 0) resolve({ id, name: "Ali" });\n    else reject(new Error("ID invalide"));\n  });\n};\n\nfetchUser(1)\n  .then(user => console.log(user))\n  .catch(err => console.error(err));</code></pre><h3>Async/await (plus lisible)</h3><pre><code>async function loadData() {\n  try {\n    const user = await fetchUser(1);\n    const posts = await fetch(`/api/posts?user=${user.id}`);\n    return await posts.json();\n  } catch (err) {\n    console.error("Erreur:", err);\n  }\n}</code></pre><h3>Parallele</h3><p>Pour executer plusieurs promises en parallele : <code>const [a, b] = await Promise.all([fetchA(), fetchB()]);</code></p>',
 30, 4, 'published'),

(@course_js, 'Modules ES6 (import/export)',
 'Organisez votre code en modules reutilisables.',
 '<h2>Export</h2><pre><code>// math.js\nexport const PI = 3.14;\nexport function add(a, b) { return a + b; }\n\n// export par defaut (un seul par module)\nexport default class Calculator { /* ... */ }</code></pre><h3>Import</h3><pre><code>// app.js\nimport Calculator, { PI, add } from "./math.js";\n// Renommage\nimport { add as addition } from "./math.js";\n// Tout importer\nimport * as math from "./math.js";\nconsole.log(math.PI);</code></pre><h3>Activation dans HTML</h3><pre><code>&lt;script type="module" src="app.js"&gt;&lt;/script&gt;</code></pre>',
 20, 5, 'published');

INSERT INTO quizzes (course_id, title, description, duration_minutes, passing_score, max_attempts) VALUES
(@course_js, 'Quiz JavaScript ES6+', 'Validez votre comprehension du JavaScript moderne.', 20, 70.00, 2);
SET @quiz_js = LAST_INSERT_ID();

INSERT INTO questions (quiz_id, question_text, question_type, points, question_order, explanation) VALUES
(@quiz_js, 'Quelle declaration empeche la reassignation de la variable ?', 'single_choice', 1.00, 1, 'const empeche la reassignation. La valeur reste immuable (mais les objets/arrays restent mutables en interne).'),
(@quiz_js, 'Les arrow functions ont leur propre contexte this.', 'true_false', 1.00, 2, 'FAUX. Les arrow functions heritent du this du contexte englobant.'),
(@quiz_js, 'Comment extraire name et age d''un objet user ?', 'single_choice', 1.50, 3, 'Le destructuring objet utilise les accolades : { prop1, prop2 } = obj.'),
(@quiz_js, 'Quelle methode execute plusieurs promises en parallele ?', 'single_choice', 1.00, 4, 'Promise.all() attend que toutes les promises se resolvent et retourne un tableau de resultats.'),
(@quiz_js, 'Quels mots-cles permettent d''exporter depuis un module ES6 ?', 'multiple_choice', 1.50, 5, 'export pour nommer et export default pour l''export principal d''un module.');

INSERT INTO responses (question_id, response_text, is_correct, response_order) VALUES
-- Q1 (id=11)
(11, 'var', 0, 1), (11, 'let', 0, 2), (11, 'const', 1, 3), (11, 'static', 0, 4),
-- Q2 (id=12) vrai/faux
(12, 'Vrai', 0, 1), (12, 'Faux', 1, 2),
-- Q3 (id=13)
(13, 'const [name, age] = user;', 0, 1), (13, 'const { name, age } = user;', 1, 2), (13, 'const (name, age) = user;', 0, 3), (13, 'const name, age = user;', 0, 4),
-- Q4 (id=14)
(14, 'Promise.run()', 0, 1), (14, 'Promise.parallel()', 0, 2), (14, 'Promise.all()', 1, 3), (14, 'Promise.async()', 0, 4),
-- Q5 (id=15) multi
(15, 'export', 1, 1), (15, 'export default', 1, 2), (15, 'module.exports', 0, 3), (15, 'public', 0, 4);


-- =====================================================
-- COURS 4 : PHP 8 & POO (Ines Sfar)
-- =====================================================
INSERT INTO courses (user_id, title, description, level, status) VALUES
(14, 'PHP 8 & Programmation Orientee Objet',
 'Apprenez PHP 8 et la programmation orientee objet moderne. Classes, heritage, interfaces, traits, namespaces et nouveautes PHP 8 (typed properties, named arguments, match expression). Construisez des applications web robustes et maintenables.',
 'intermediate', 'published');
SET @course_php = LAST_INSERT_ID();

INSERT INTO lessons (course_id, title, summary, content, duration_minutes, lesson_order, status) VALUES
(@course_php, 'Les classes en PHP 8',
 'Creer ses premieres classes avec proprietes typees et methodes.',
 '<h2>Definition d''une classe</h2><pre><code>&lt;?php\nclass User {\n    // Proprietes typees (PHP 7.4+)\n    public int $id;\n    public string $name;\n    private ?string $email = null;\n\n    public function __construct(int $id, string $name) {\n        $this-&gt;id = $id;\n        $this-&gt;name = $name;\n    }\n\n    public function setEmail(string $email): void {\n        $this-&gt;email = $email;\n    }\n\n    public function getEmail(): ?string {\n        return $this-&gt;email;\n    }\n}\n\n$user = new User(1, "Ali");\n$user-&gt;setEmail("ali@test.com");</code></pre><h3>Constructor promotion (PHP 8)</h3><pre><code>class User {\n    public function __construct(\n        public int $id,\n        public string $name,\n        private ?string $email = null\n    ) {}\n}</code></pre>',
 25, 1, 'published'),

(@course_php, 'Heritage et polymorphisme',
 'Reutiliser et specialiser des classes existantes.',
 '<h2>Heritage avec extends</h2><pre><code>class Animal {\n    public function __construct(public string $name) {}\n    public function speak(): string {\n        return "Un son";\n    }\n}\n\nclass Dog extends Animal {\n    public function speak(): string {\n        return "Woof!";\n    }\n    public function fetch(): string {\n        return "{$this-&gt;name} ramene la balle";\n    }\n}\n\n$dog = new Dog("Rex");\necho $dog-&gt;speak(); // Woof!\necho $dog-&gt;fetch(); // Rex ramene la balle</code></pre><h3>Mot-cle parent</h3><pre><code>class Cat extends Animal {\n    public function speak(): string {\n        return parent::speak() . " (miaou)";\n    }\n}</code></pre>',
 22, 2, 'published'),

(@course_php, 'Interfaces et traits',
 'Definir des contrats et partager du code entre classes.',
 '<h2>Interface (contrat)</h2><pre><code>interface Payable {\n    public function pay(float $amount): bool;\n    public function getBalance(): float;\n}\n\nclass BankAccount implements Payable {\n    private float $balance = 0;\n\n    public function pay(float $amount): bool {\n        if ($amount &gt; $this-&gt;balance) return false;\n        $this-&gt;balance -= $amount;\n        return true;\n    }\n\n    public function getBalance(): float {\n        return $this-&gt;balance;\n    }\n}</code></pre><h3>Trait (code reutilisable)</h3><pre><code>trait Timestampable {\n    public ?DateTime $createdAt = null;\n    public ?DateTime $updatedAt = null;\n\n    public function touch(): void {\n        $this-&gt;updatedAt = new DateTime();\n    }\n}\n\nclass Post {\n    use Timestampable;\n    // ...\n}</code></pre>',
 28, 3, 'published'),

(@course_php, 'Nouveautes PHP 8',
 'Decouvrez les fonctionnalites majeures introduites en PHP 8.',
 '<h2>Match expression (alternative a switch)</h2><pre><code>$status = match($code) {\n    200, 201 =&gt; "Success",\n    404      =&gt; "Not Found",\n    500, 502 =&gt; "Server Error",\n    default  =&gt; "Unknown"\n};</code></pre><h3>Named arguments</h3><pre><code>function createUser(string $name, int $age = 18, string $role = "user") {}\n\n// Avant : ordre obligatoire\ncreateUser("Ali", 25, "admin");\n// PHP 8 : ordre libre, nommage explicite\ncreateUser(name: "Ali", role: "admin");</code></pre><h3>Nullsafe operator</h3><pre><code>// Avant\n$country = $user-&gt;getAddress() ? $user-&gt;getAddress()-&gt;getCountry() : null;\n// PHP 8\n$country = $user?-&gt;getAddress()?-&gt;getCountry();</code></pre>',
 20, 4, 'published');

INSERT INTO quizzes (course_id, title, description, duration_minutes, passing_score, max_attempts) VALUES
(@course_php, 'Quiz PHP 8 & POO', 'Verifiez votre comprehension des classes, heritage et nouveautes PHP 8.', 18, 65.00, 3);
SET @quiz_php = LAST_INSERT_ID();

INSERT INTO questions (quiz_id, question_text, question_type, points, question_order, explanation) VALUES
(@quiz_php, 'Quel mot-cle permet d''heriter d''une autre classe en PHP ?', 'single_choice', 1.00, 1, 'extends est le mot-cle d''heritage. implements sert pour les interfaces.'),
(@quiz_php, 'Une classe peut implementer plusieurs interfaces.', 'true_false', 1.00, 2, 'VRAI. Contrairement a l''heritage de classe (single), une classe peut implementer plusieurs interfaces separees par des virgules.'),
(@quiz_php, 'Comment definir une propriete typee privee en PHP 8 ?', 'single_choice', 1.50, 3, 'La syntaxe PHP 8 typee est : private type $nom. L''ordre est important.'),
(@quiz_php, 'Quelle est la nouveaute PHP 8 pour gerer les nullables ?', 'single_choice', 1.00, 4, 'L''operateur nullsafe ?-> permet d''appeler des methodes sur des objets potentiellement null sans erreur.'),
(@quiz_php, 'Quelles instructions PHP 8 sont valides ?', 'multiple_choice', 1.50, 5, 'Les named arguments (param: value) et constructor promotion (public dans le constructor) sont PHP 8.');

INSERT INTO responses (question_id, response_text, is_correct, response_order) VALUES
-- Q1 (id=16)
(16, 'inherit', 0, 1), (16, 'extends', 1, 2), (16, 'implements', 0, 3), (16, 'parent', 0, 4),
-- Q2 (id=17) vrai/faux
(17, 'Vrai', 1, 1), (17, 'Faux', 0, 2),
-- Q3 (id=18)
(18, 'private $name: string;', 0, 1), (18, 'private string $name;', 1, 2), (18, '$name = private string;', 0, 3), (18, 'string private $name;', 0, 4),
-- Q4 (id=19)
(19, 'try-catch', 0, 1), (19, 'isset()', 0, 2), (19, 'L''operateur nullsafe ?-&gt;', 1, 3), (19, 'empty()', 0, 4),
-- Q5 (id=20) multi
(20, 'createUser(name: "Ali")', 1, 1), (20, 'function __construct(public int $id) {}', 1, 2), (20, 'class Foo<T> {}', 0, 3), (20, 'echo "abc" + 5;', 0, 4);


-- =====================================================
-- COURS 5 : Python pour Data Science (Nabil Gharbi) - DRAFT
-- =====================================================
INSERT INTO courses (user_id, title, description, level, status) VALUES
(11, 'Python pour Data Science',
 'Decouvrez Python et son ecosysteme pour l''analyse de donnees. Pandas, NumPy, Matplotlib et premieres notions de machine learning avec scikit-learn. Niveau intermediaire, bases Python recommandees.',
 'intermediate', 'draft');
SET @course_py = LAST_INSERT_ID();

INSERT INTO lessons (course_id, title, summary, content, duration_minutes, lesson_order, status) VALUES
(@course_py, 'Introduction a NumPy',
 'Manipuler des tableaux numeriques performants.',
 '<h2>NumPy</h2><p>NumPy est la bibliotheque fondamentale pour le calcul scientifique en Python. Elle fournit l''objet <code>ndarray</code>, un tableau multidimensionnel ultra-performant.</p><pre><code>import numpy as np\n\narr = np.array([1, 2, 3, 4, 5])\nprint(arr.mean())  # 3.0\nprint(arr * 2)     # [2 4 6 8 10]\n\n# Matrice 2D\nmatrix = np.array([[1, 2, 3], [4, 5, 6]])\nprint(matrix.shape)  # (2, 3)\nprint(matrix.T)      # transposee</code></pre>',
 30, 1, 'published'),

(@course_py, 'Pandas - DataFrames',
 'Manipuler des donnees tabulaires avec Pandas.',
 '<h2>Pandas DataFrames</h2><pre><code>import pandas as pd\n\ndf = pd.read_csv("data.csv")\nprint(df.head())\nprint(df.describe())\n\n# Filtrage\nadults = df[df["age"] &gt;= 18]\n\n# Groupby\nstats = df.groupby("city")["income"].mean()\n\n# Pivot\npivot = df.pivot_table(values="sales", index="month", columns="product")</code></pre>',
 35, 2, 'published'),

(@course_py, 'Visualisation avec Matplotlib',
 'Creer des graphiques pour explorer ses donnees.',
 '<h2>Matplotlib basique</h2><pre><code>import matplotlib.pyplot as plt\n\nplt.figure(figsize=(10, 6))\nplt.plot(x, y, label="Ventes")\nplt.bar(months, revenue)\nplt.scatter(age, income)\n\nplt.title("Evolution des ventes")\nplt.xlabel("Mois")\nplt.ylabel("Chiffre d''affaires")\nplt.legend()\nplt.grid(True)\nplt.show()</code></pre>',
 25, 3, 'draft');

-- Pas de quiz pour le cours draft (sera ajoute quand publie)


-- =====================================================
-- 2 CERTIFICATS DEMO (pour tester l'affichage)
-- =====================================================
-- Etudiant abdennadher yassmine (id 31 si encadrant, sinon un etudiant existant)
INSERT INTO certificates (user_id, course_id, quiz_id, student_name, issued_at) VALUES
(NULL, @course_html, @quiz_html, 'Ahmed Ben Salem', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(NULL, @course_css,  @quiz_css,  'Fatma Mansouri',  DATE_SUB(NOW(), INTERVAL 2 DAY));


-- =====================================================
-- Verification finale
-- =====================================================
SELECT '=== COURSES ===' AS info;
SELECT id, title, level, status, (SELECT COUNT(*) FROM lessons WHERE course_id = courses.id) AS nb_lessons, (SELECT COUNT(*) FROM quizzes WHERE course_id = courses.id) AS nb_quizzes FROM courses;

SELECT '=== LESSONS ===' AS info;
SELECT course_id, COUNT(*) AS nb_lessons FROM lessons GROUP BY course_id;

SELECT '=== QUIZZES + QUESTIONS ===' AS info;
SELECT q.id, q.title, q.course_id, (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) AS nb_questions FROM quizzes q;

SELECT '=== CERTIFICATES ===' AS info;
SELECT id, student_name, course_id, quiz_id FROM certificates;
