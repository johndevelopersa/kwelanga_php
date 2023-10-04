<?php


class DearProductResponseObj extends DearBaseObj
{
    protected $Total; //int
    protected $Page; //int
    protected $Products; //array

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->Total;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->Page;
    }

    /**
     * @return DearProductItemObj[]
     */
    public function getProducts(): array
    {
        $products = [];
        foreach($this->Products as $product){
            $products[] = new DearProductItemObj($product);
        }
        return $products;
    }
}
