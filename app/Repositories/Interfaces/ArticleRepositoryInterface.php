<?php

interface ArticleRepositoryInterface
{
    public function all();
    public function mine($userId);
    public function store(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function publish($id);
}
