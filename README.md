ShayganAffiliateBundle
===================
A simple **Symfony Affiliate Bundle** compatible with **Symfony 6.4+ and 7.x**.

This ~~FOSUserBundle compatible~~ Bundle tracks referrals with query string
parameter and detect referred registrations via cookie. You can easily ask
the bundle for commission amount (if there is any referrer) and apply the amount
to referrer's user account.

Your Order Object needs to implements \Shaygan\AffiliateBundle\Model\OrderInterface.

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or 7.x
- Doctrine ORM 2.10+ or 3.x

## Install

Via Composer

``` bash
$ composer require shaygan/affiliate-bundle
```

### Symfony 6.4+ / 7.x

If you're using Symfony Flex, the bundle will be automatically registered. Otherwise, register it manually in `config/bundles.php`:

```php
return [
    // ...
    Shaygan\AffiliateBundle\ShayganAffiliateBundle::class => ['all' => true],
];
```

### Older Symfony Versions

For Symfony 4.4/5.x, please use an earlier version of this bundle.

## Configure the bundle

This bundle was designed to just work out of the box. The only thing you have to configure in order to get this bundle up and running is your commission type amount and count.

```yaml
# config/packages/shaygan_affiliate.yaml

shaygan_affiliate:
    programs:
        membership_upgrade:
            type: fixed-amount
            first_commission_amount: 3
            commission_amount: 1
            max_count: 10
        purchase:
            type: percentage
            first_commission_percent: 15
            commission_percent: 10
            max_count: 10
```
By default it look for ?ref=REFERRER_ID in URLs the *ref* is configurable

## Usage

ShayganAffiliateBundel tracks FOSUserBundle registrations automatically and after every purchase you just need to ask the bundle for the commission payment if the referrer eligible for commission the bundle with return a commission Entity that contain the amount you have to pay to the referrer, othervise returns null.


```php
  // ... 
  // MyOrder SHOULD implements \Shaygan\AffiliateBundle\Model\OrderInterface
  $order = new \AppBundle\Entity\MyOrder();
  $order->setOwnerUser($this->getUser());

  $em = $this->getDoctrine()->getManager();
  $em->persist($order);
  $em->flush();
  $affiliate = $this->get("shaygan_affiliate");
  $commission = $affiliate->getPurchaseCommission($order);
  if(null !== $commission){
    $commissionAmount = $commission->getCommissionAmount();
    $referrer = $commission->getReferrer();
    $referrerUserId = $referrer->getId();
    // You can apply the commission amount to the accont of referrer User here
    // ...
  }
  //...
```

## Testing

Not implemented yet

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
