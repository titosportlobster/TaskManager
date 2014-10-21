<?php

namespace TitoMiguelCosta\TaskManager\Storage;

class Criteria
{
    protected $name = null;
    protected $category = null;
    protected $status = null;
    protected $page = null;
    protected $limit = null;

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
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param null $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
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
