<?php

use \Esensi\Model\Model;
use \Illuminate\Hashing\BcryptHasher;
use \Mockery;
use \PHPUnit_Framework_TestCase as PHPUnit;


/**
 * Tests for the Hashing Model Trait
 *
 * @package Esensi\Model
 * @author Daniel LaBarge <wishlist@emersonmedia.com>
 * @copyright 2014 Emerson Media LP
 * @license https://github.com/esensi/model/blob/master/LICENSE.txt MIT License
 * @link http://www.emersonmedia.com
 */
class HashingModelTraitTest extends PHPUnit {

    /**
     * Set Up and Prepare Tests.
     */
    public function setUp()
    {
        // Mock the Model that uses the custom trait
        $this->model = Mockery::mock('ModelHashingStub');
        $this->model->makePartial();
    }

    /**
     * Tear Down and Clean Up Tests.
     */
    public function tearDown()
    {
        Mockery::close();
    }

    /**
     * Enables Hashing on mock.
     */
    protected function enableHashingOnMock()
    {
        $this->model->shouldReceive('getHashing')
            ->atleast()
            ->times(1)
            ->andReturn(true);
    }

    /**
     * Disables Hashing on mock.
     */
    protected function disableHashingOnMock()
    {
        $this->model->shouldReceive('getHashing')
            ->atleast()
            ->times(1)
            ->andReturn(false);
    }

    /**
     * Sets the Hasher on mock.
     */
    public function setHasherOnMock()
    {
        $this->model->setHasher(new HasherStub());
    }

    /**
     * Test that Hashing is enabled by default.
     */
    public function testHashingEnabledByDefault()
    {
        $this->assertTrue($this->model->getHashing());
    }

    /**
     * Test that Hashing can be enabled and disabled.
     */
    public function testSettingHashing()
    {
        // Disable Hashing
        $this->model->setHashing(false);
        $this->assertFalse($this->model->getHashing());

        // Enable Hashing
        $this->model->setHashing(true);
        $this->assertTrue($this->model->getHashing());
    }

    /**
     * Test that Hashable attributes can be gotten.
     */
    public function testGettingHashableAttributes()
    {
        // Get the attributes
        $attributes = $this->model->getHashable();

        // Check that its an array
        $this->assertTrue(is_array($attributes));

        // Check that it returned the default value
        $this->assertContains('foo', $attributes);

        // Check that the count matches
        $this->assertCount(1, $attributes);
    }

    /**
     * Test that Hashable attributes can be set.
     */
    public function testSettingHashableAttributes()
    {
        // Set the attributes
        $this->model->setHashable(['bar']);

        // Get the attributes
        $attributes = $this->model->getHashable();

        // Check that its an array
        $this->assertTrue(is_array($attributes));

        // Check that it returned the set value
        $this->assertContains('bar', $attributes);

        // Check that the count matches
        $this->assertCount(1, $attributes);
    }

    /**
     * Test that the Hasher can be gotten.
     */
    public function testGettingHasher()
    {
        // Check that its an Hasher
        $model = new ModelHashingStub();
        $hasher = $model->getHasher();
        $this->assertInstanceOf('\Illuminate\Hashing\BcryptHasher', $hasher, $hasher);
    }

    /**
     * Test that the Hasher can be set.
     */
    public function testSettingHasher()
    {
        // Set the Hasher
        $this->model->setHasher(new HasherStub());

        // Check that its an Hasher stub
        $this->assertInstanceOf('HasherStub', $this->model->getHasher());
    }

    /**
     * Test that isHashable returns true when hashing is enabled
     * and the attribute is Hashable.
     */
    public function testIsHashableReturnsTrue()
    {
        // Enable hashing
        $this->enableHashingOnMock();

        // Make sure getHashable is called and attribute is hashable
        $this->model->shouldReceive('getHashable')
            ->once()
            ->andReturn(['foo']);

        // Check that the attribute is hashable
        $this->assertTrue($this->model->isHashable('foo'));
    }

    /**
     * Test that non-Hashable attribute is not Hashable even
     * when Hashing is enabled.
     */
    public function testIsHashableReturnsFalseWhenNotSet()
    {
        // Enable hashing
        $this->enableHashingOnMock();

        // Make sure getHashable is called and attribute is not hashable
        $this->model->shouldReceive('getHashable')
            ->once()
            ->andReturn(['bar']);

        // Check that the attribute is not hashable
        $this->assertFalse($this->model->isHashable('foo'));
    }

    /**
     * Test that Hashable attribute is not Hashable when
     * Hashing is disabled.
     */
    public function testIsHashableReturnsFalseWhenDisabled()
    {
        // Disable hashing
        $this->disableHashingOnMock();

        // Make sure getHashable is called and attribute is hashable
        $this->model->shouldReceive('getHashable')
            ->andReturn(['foo']);

        // Check that the attribute is not hashable
        $this->assertFalse($this->model->isHashable('foo'));
    }

    /**
     * Test that isHashed returns true when attribute value is hashed.
     */
    public function testIsHashedReturnsTrue()
    {
        // Set the Hasher and hash a plain text value
        $this->setHasherOnMock();
        $hashed = $this->model->hash('plain text');

        // Set the attribute's hashed value
        $this->model->setRawAttributes(['foo' => $hashed]);

        // Check that the attribute is hashed
        $this->assertTrue($this->model->isHashed('foo'));
    }

    /**
     * Test that isHashed returns false when attribute value
     * is not hashed or otherwise doesn't exist.
     */
    public function testIsHashedReturnsFalse()
    {
        // Check that attribute is not hashed because it doesn't exist
        $this->assertFalse($this->model->isHashed('foo'));

        // Set an attribute's plain value
        $this->model->setRawAttributes(['foo' => 'plain text']);

        // Check that the attribute is not hashed
        $this->assertFalse($this->model->isHashed('foo'));
    }

    /**
     * Test that all hashable attributes are hashed.
     */
    public function testHashAttributes()
    {
        $hashables = ['foo', 'bar'];
        $this->model->setHashable($hashables);

        // Accessor should be used when getting the attribute value
        $this->model->shouldReceive('getAttribute')
            ->times(count($hashables));

        // Make sure the mutator is called for each attribute
        $this->model->shouldReceive('setHashingAttribute')
            ->times(count($hashables));

        // Do it
        $this->model->hashAttributes();
    }

    /**
     * Test that hash() hashes the value.
     */
    public function testHash()
    {
        $this->setHasherOnMock();
        $hashed = $this->model->hash('plain text');
        $this->assertNotEquals($hashed, 'plain text');
    }

    /**
     * Test that checkHash() compares a hash with the plain text.
     */
    public function testCheckHash()
    {
        $this->setHasherOnMock();
        $hashed = $this->model->hash('plain text');
        $this->assertTrue($this->model->checkHash('plain text', $hashed));
        $this->assertFalse($this->model->checkHash('plain text', 'foo'));
    }

    /**
     * Test that setHashingAttribute() hashes the attribute value.
     */
    public function testSetHashingAttribute()
    {
        $hashables = ['foo' => 'fighter', 'bar' => 'soap'];
        $this->setHasherOnMock();
        $this->model->unguard();
        $this->model->fill($hashables);
        $this->model->setHashable(array_keys($hashables));

        // Make sure each attribute is dirty before hashing
        $this->model->shouldReceive('isDirty')
            ->times(2)
            ->andReturn(true);

        // Do it
        $this->model->hashAttributes();

        // Check if getting the hashable attribute returns the hashed value
        $hashed = $this->model->getAttribute('foo');
        $this->assertNotEquals('plain text', $hashed);
        $this->assertEquals($hashed, $this->model->foo);

        // Check that the attribute is hashed
        $this->assertTrue($this->model->isHashed('foo'));
    }

}

/**
 * Model Stub for Hashing Tests
 */
class ModelHashingStub extends Model {

    /**
     * The attributes to hash before saving.
     *
     * @var array
     */
    protected $hashable = ['foo'];

    /**
     * Create a new model instance
     *
     * @return ModelHashingStub
     */
    public function __construct(){

        parent::__construct();

        // Assign a default hasher for mocking purposes
        $this->hasher = new BcryptHasher();
    }

}

/**
 * Hasher Stub for Hasher Tests
 */
class HasherStub extends BcryptHasher {

}