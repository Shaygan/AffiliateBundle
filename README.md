ShayganAffiliateBundle
===================
**Notice: this bundle is under heavy development**

A simple **Symfony 2 Affiliate Bundle**.

This FOSUserBundle compatible Bundle tracks referrals with query string 
parameter and detect referred registrations via cookie. You can easil ask
the bundle for commission amount (if there is any referrer) and apply the amount
to referrer's user account. 

Your Order Object needs to implements \Shaygan\AffiliateBundle\Model\OrderInterface.


## Install

Via Composer

``` bash
$ composer require shaygan/affiliate-bundle
```

Edit your app/AppKernel.php to register the bundle in the registerBundles() method as above:


```php
class AppKernel extends Kernel
{

    public function registerBundles()
    {
        $bundles = array(
            // ...
            // register the bundle here
            new \Shaygan\AffiliateBundle\ShayganAffiliateBundle()
        );
    }
}
```

## Configure the bundle

This bundle was designed to just work out of the box. The only thing you have to configure in order to get this bundle up and running is your commission type amount and count.

```yaml
# app/config/config.yml

shaygan_affiliate:
    purchase:
        type: percent
        persent: 30
        max_count: 1
```
By default it look for ?ref=REFERRER_ID in URLs the *ref* is configurable

## Usage

ShayganAffiliateBundel tracks FOSUserBundle registrations automatically and after every purchase you just need to ask the bundle for the commission payment if the referrer eligible for commission the bundle with return a commission Entity that contain the amount you have to pay to the referrer, othervise returns null.


```php
<?php
  // ...
  
  // MyOrder SHOULD implements \Shaygan\AffiliateBundle\Model\OrderInterface
  $order = new \AppBundle\Entity\MyOrder();
  $order->setOwnerUser($this->getUser());

  $em = $this->getDoctrine()->getManager();
  $em->persist($order);
  $em->flush();
  $affiliate = $this->get("shaygan_affiliate.affiliate");
  $commission = $affiliate->getPurchaseCommission($order);
  if(null !== $commission){
    $commissionAmount = $commisiion->getCommissionAmount();
    $referrer = $commisiion->getReferrer();
    $referrerUserId = $referrer->getId()
    // You can apply the commission amount to the accont of referrer User here
    // ...
  }
  //...
?>
```

## Testing

Not implemented yet

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
