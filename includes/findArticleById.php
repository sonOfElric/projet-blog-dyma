<?php

declare(strict_types=1);

function findArticleById(int $id)
{
    $filename = __DIR__ . '/data/articles.json';

    if (file_exists($filename)) {
        $articles = json_decode(file_get_contents($filename), true) ?? [];
        $articleIndex = array_search($id, array_column($articles, 'id'));
        $article = $articles[$articleIndex];
        return $article;
    }
}
