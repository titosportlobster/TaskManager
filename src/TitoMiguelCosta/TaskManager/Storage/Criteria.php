<?php

namespace TitoMiguelCosta\TaskManager\Storage;

class Criteria
{
    protected $name = null;
    protected $category = null;
    protected $status = null;
    protected $page = null;
    protected $count = null;

    /**
     * @param null $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param null $limit
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @return null
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param null $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return null
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param null $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return null
     */
    public function getStatus()
    {
        return $this->status;
    }

}
