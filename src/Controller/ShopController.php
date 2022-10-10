<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    #[Route('/shop', name: 'app_shop')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy(['active' => true]);

        return $this->render('shop/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/shop/cart-add/{product}', name: 'app_shop_cart_add')]
    public function cartAdd(Product $product, EntityManagerInterface $entityManagerInterface): Response
    {
        /** @var User */
        $user = $this->getUser();

        $cart = $user->getCurrentCart();

        $itemRepo = $entityManagerInterface->getRepository(CartItem::class);

        $item = $itemRepo->findOneBy(['cart' => $cart, 'product' => $product]);
        
        if ($item === null) {
            $item = new CartItem;
            $item
                ->setProduct($product)
                ->setPrice($product->getActivePrices()->first())
                ->setQuantity(1)
            ;
        } else {
            $qtt = $item->getQuantity() + 1;
            $item->setQuantity($qtt);
        }

        $cart->addItem($item);

        $entityManagerInterface->persist($cart);
        $entityManagerInterface->flush();

        $this->addFlash('success', 'Added one '.$product->getName().' to your cart');

        return $this->redirectToRoute('app_shop');
    }
}
