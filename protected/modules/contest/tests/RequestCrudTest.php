<?php

use contest\models\Request;
use contest\crud\RequestCrud;
use ext\test\ARTestHelper;

class RequestCrudTest extends \CTestCase
{
    protected static $ar;

    public static function setUpBeforeClass()
    {
        self::$ar = new ARTestHelper();
    }

    public static function tearDownAfterClass()
    {
        self::$ar = null;
    }

    public function setUp()
    {
        self::$ar->setUp($this);
    }

    public function tearDown()
    {
        self::$ar->tearDown();
    }

    public function testAcceptSoloRequest()
    {
        $requestCrud = new RequestCrud();

        self::$ar->willFind([
            'id' => '-100',
            'contest_id' => '1',
            'status' => '1',
            'contact_name' => 'foo',
            'contact_email' => 'foo@bar.com',
            'contact_phone' => '+38 (000) 000-00-00',
            'age_category' => Request::AGE_CATEGORY_10,
            'format' => Request::FORMAT_INSTRUMENTAL_SOLO,
        ]);

        self::$ar->willSave([
            'status' => Request::STATUS_ACCEPTED,
        ]);

        $requestCrud->accept(1);
    }

    public function testAcceptGroupRequest()
    {
        $requestCrud = new RequestCrud();

        self::$ar->willFind([
            'id' => '-100',
            'contest_id' => '1',
            'status' => '1',
            'name' => 'NoName',
            'contact_name' => 'foo',
            'contact_email' => 'foo@bar.com',
            'contact_phone' => '+38 (000) 000-00-00',
            'age_category' => Request::AGE_CATEGORY_10,
            'format' => Request::FORMAT_GROUP,
        ]);

        self::$ar->willSave([
            'status' => Request::STATUS_ACCEPTED,
        ]);

        $requestCrud->accept(1);
    }

    public function testDeclineSoloRequest()
    {
        $requestCrud = new RequestCrud();

        self::$ar->willFind([
            'id' => '-100',
            'contest_id' => '1',
            'status' => '1',
            'contact_name' => 'foo',
            'contact_email' => 'foo@bar.com',
            'contact_phone' => '+38 (000) 000-00-00',
            'age_category' => Request::AGE_CATEGORY_10,
            'format' => Request::FORMAT_INSTRUMENTAL_SOLO,
        ]);

        self::$ar->willSave([
            'status' => Request::STATUS_DECLINED,
        ]);

        $requestCrud->decline(1);
    }

    public function testDeclineGroupRequest()
    {
        $requestCrud = new RequestCrud();

        self::$ar->willFind([
            'id' => -100,
            'contest_id' => '1',
            'status' => '1',
            'name' => 'NoName',
            'contact_name' => 'foo',
            'contact_email' => 'foo@bar.com',
            'contact_phone' => '+38 (000) 000-00-00',
            'age_category' => Request::AGE_CATEGORY_10,
            'format' => Request::FORMAT_GROUP,
        ]);

        self::$ar->willSave([
            'status' => Request::STATUS_DECLINED,
        ]);

        $requestCrud->decline(1);
    }
}
