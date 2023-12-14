
<?php 
$index = 1;
foreach ($model->purchaseOrderProducts as $k => $orderHasProduct)
{
    if ($index > 1)  echo("\r\n");
    if ($orderHasProduct->purchaseProduct->purchase_date == $date)
    {
        $text = $index . ") " . $orderHasProduct->name;
        $quantity = $orderHasProduct->purchaseProduct->is_weights ? $orderHasProduct->quantity : number_format($orderHasProduct->quantity);
        $text .= " - " .$quantity . ' x ' . $orderHasProduct->price . ' = ' . $orderHasProduct->total;
        echo($text);
    }
    $index++;
}
?>