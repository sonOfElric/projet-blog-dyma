<?php

$pdo =  require_once __DIR__ . '/database/database.php';
$authDB = require_once __DIR__ . '/database/security.php';

$currentUser = $authDB->isLoggedIn();

if (!$currentUser) {
    header('Location: /');
}
$articleDB = require_once('./database/models/ArticleDB.php');


const ERROR_REQUIRED = 'Veuillez renseigner ce champ';
const ERROR_TITLE_TOO_SHORT = 'Le titre est trop court';
const ERROR_TITLE_TOO_LONG = 'Le titre est trop long';
const ERROR_CONTENT_TOO_SHORT = 'L\'article est trop court';
const ERROR_CONTENT_TOO_LONG = 'L\'article est trop long';
const ERROR_IMAGE_URL = 'L\'image doit être une url valide';
const ERROR_URL_TOO_LONG = 'L\'url est trop longue';
const ERROR_CATEGORY_DOES_NOT_EXISTS = 'La catégorie n\'existe pas';

$filename = __DIR__ . '/data/articles.json';

$errors = [
    'title' => '',
    'image' => '',
    'category' => '',
    'content' => ''
];


$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$id = $_GET['id'] ?? '';
if ($id) {


    $article = $articleDB->fetchOne($id);
    if ($currentUser['id'] !== $article['author']) {
        header('Location: /');
    }
    $title = $article['title'] ?? '';
    $image = $article['image'] ?? '';
    $category = $article['category'] ?? '';
    $content = $article['content'] ?? '';
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $_POST = filter_input_array(INPUT_POST, [
        'title' => FILTER_SANITIZE_STRING,
        'image' => FILTER_SANITIZE_URL,
        'category' => FILTER_SANITIZE_STRING,
        'content' => [
            'filter' => FILTER_SANITIZE_STRING,
            'flags' => FILTER_FLAG_NO_ENCODE_QUOTES
        ]
    ]);

    $title = $_POST['title'] ?? '';
    $image = $_POST['image'] ?? '';
    $category = $_POST['category'] ?? '';
    $content = $_POST['content'] ?? '';

    if (!$title) {
        $errors['title'] = ERROR_REQUIRED;
    } elseif (mb_strlen($title) < 5) {
        $errors['title'] = ERROR_TITLE_TOO_SHORT;
    } elseif (mb_strlen($title) > 80) {
        $errors['title'] = ERROR_TITLE_TOO_LONG;
    }

    if (!$image) {
        $errors['image'] = ERROR_REQUIRED;
    } elseif (!filter_var($image, FILTER_VALIDATE_URL)) {
        $errors['image'] = ERROR_IMAGE_URL;
    }
    // } elseif (mb_strlen($image) > 60) {
    //     $errors['image'] = ERROR_URL_TOO_LONG;
    // }


    if (!$category) {
        $errors['category'] = ERROR_REQUIRED;
    }
    // elseif (!array_search($category, array_column($article, 'category'))) {
    //     $errors['category'] = ERROR_CATEGORY_DOES_NOT_EXISTS;
    // }

    if (!$content) {
        $errors['content'] = ERROR_REQUIRED;
    } elseif (mb_strlen($content) < 50) {
        $errors['content'] = ERROR_CONTENT_TOO_SHORT;
    }
    // } elseif (mb_strlen($content) > 200) {
    //     $errors['content'] = ERROR_CONTENT_TOO_LONG;
    // }

    if (empty(array_filter($errors, fn ($e) => $e !== ''))) {
        if ($id) {
            $article['title'] = $title;
            $article['image'] = $image;
            $article['category'] = $category;
            $article['content'] = $content;
            $article['author'] = $currentUser['id'];
            $articleDB->updateOne($article);
        } else {
            $articleDB->createOne([
                'title' => $title,
                'content' => $content,
                'category' => $category,
                'image' => $image,
                'author' => $currentUser['id']
            ]);
        }
        header('Location: /');
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once 'includes/head.php'; ?>
    <!-- <link rel="stylesheet" href="/public/css/form-article.css"> -->
    <title><?= $id ? 'Modifier' : 'Créer' ?> un article</title>
</head>

<body>
    <div class="container">
        <?php require_once 'includes/header.php'; ?>
        <div class="content">
            <div class="block p-20 form-container">
                <h1><?= $id ? 'Modifier' : 'Ecrire' ?> un article</h1>
                <form action="/form-article.php<?= $id ? "?id=$id" : '' ?>" method="post">
                    <div class="form-control">
                        <label for="title">Titre</label>
                        <input type="text" name="title" id="title" value="<?= $title ?? '' ?>">
                        <?php if ($errors['title']) : ?>
                            <p class="text-danger"><?= $errors['title'] ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-control">
                        <label for="title">Image</label>
                        <input type="text" name="image" id="image" value="<?= $image ?? '' ?>">
                        <?php if ($errors['image']) : ?>
                            <p class="text-danger"><?= $errors['image'] ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-control">
                        <label for="title">Catégorie</label>
                        <select name="category" id="category">
                            <option <?= !$category || $category === 'technology' ? 'selected' : '' ?> value="technology">Technologie</option>
                            <option <?= $category || $category === 'nature' ? 'selected' : '' ?> value="nature">Nature</option>
                            <option <?= $category || $category === 'politic' ? 'selected' : '' ?> value="politic">Politique</option>
                        </select>
                        <?php if ($errors['category']) : ?>
                            <p class="text-danger"><?= $errors['category'] ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-control">
                        <label for="title">Content</label>
                        <textarea name="content" id="content"><?= $content ?? '' ?></textarea>
                        <?php if ($errors['content']) : ?>
                            <p class="text-danger"><?= $errors['content'] ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-action">
                        <a href="/">
                            <button class="btn btn-secondary" type="button">Annuler</button>
                        </a>
                        <button class="btn btn-primary" type="submit"><?= $id ? 'Modifier' : 'Sauvegarder' ?></button>
                    </div>
                </form>
            </div>
        </div>
        <?php require_once 'includes/footer.php'; ?>


    </div>

</body>

</html>