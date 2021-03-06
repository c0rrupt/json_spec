<?php

namespace spec\JsonSpec;

use JsonSpec\Helper\JsonHelper;
use JsonSpec\MatcherOptions;
use JsonSpec\MatcherOptionsFactory;
use PhpSpec\ObjectBehavior;
use Seld\JsonLint\JsonParser;

class JsonSpecMatcherSpec extends ObjectBehavior
{

    public function let(MatcherOptionsFactory $factory, MatcherOptions $options)
    {
        $options->getPath()->willReturn(null);
        $options->getExcludedKeys()->willReturn(array('id'));

        $factory->createOptions()->willReturn($options);
        $this->beConstructedWith(new JsonHelper(new JsonParser()), $factory);
    }

    // <editor-fold desc="isEqual spec">
    public function it_matches_identical_JSON()
    {
        $this->isEqual('{"json":"spec"}', '{"json":"spec"}')->shouldBe(true);
    }

    public function it_matches_differently_formatted_JSON()
    {
        $this->isEqual('{"json": "spec"}', '{"json":"spec"}')->shouldBe(true);
    }

    public function it_matches_out_of_order_hashes()
    {
        $this->isEqual('{"laser":"lemon","json":"spec"}', '{"json":"spec","laser":"lemon"}')->shouldBe(true);
    }

    public function it_does_not_match_out_of_order_arrays()
    {
        $this->isEqual('["json","spec"]', '["spec", "json"]')->shouldBe(false);
    }

    public function it_matches_valid_JSON_values_yet_invalid_JSON_documents(MatcherOptions $options)
    {
        $this->isEqual('"json_spec"', '"json_spec"')->shouldBe(true);
    }

    public function it_matches_at_a_path(MatcherOptions $options)
    {
        $options->getPath()->willReturn('json/0');
        $this->isEqual('{"json":["spec"]}', '"spec"')->shouldBe(true);
    }

    public function it_ignores_excluded_by_default_hash_keys()
    {
        $this->isEqual('{"id": 1, "json":["spec"]}', '{"id": 2, "json":["spec"]}')->shouldBe(true);
    }

    public function it_ignores_custom_excluded_hash_keys(MatcherOptions $options)
    {
        $options->getExcludedKeys()->willReturn(array('ignore'));
        $this->isEqual('{"json":"spec","ignore":"please"}', '{"json":"spec"}')->shouldBe(true);
    }

    public function it_ignores_nested_excluded_hash_keys(MatcherOptions $options)
    {
        $options->getExcludedKeys()->willReturn(array('ignore'));
        $this->isEqual('{"json":"spec","please":{"ignore":"this"}}', '{"json":"spec","please":{}}')->shouldBe(true);
    }

    public function it_ignores_hash_keys_when_included_in_the_expected_value(MatcherOptions $options)
    {
        $options->getExcludedKeys()->willReturn(array('ignore'));
        $this->isEqual('{"json":"spec","ignore":"please"}', '{"json":"spec","ignore":"this"}')->shouldBe(true);
    }

    public function it_matches_different_looking_JSON_equivalent_values(MatcherOptions $options)
    {
        $this->isEqual('{"ten":10.0}', '{"ten":1e+1}')->shouldBe(true);
    }

    public function it_excludes_multiple_keys(MatcherOptions $options)
    {
        $options->getExcludedKeys()->willReturn(array('id', 'json'));
        $this->isEqual('{"id":1,"json":"spec"}', '{"id":2,"json":"different"}')->shouldBe(true);
    }
    //</editor-fold>

    // <editor-fold desc="havePath spec">
    public function it_matches_hash_keys()
    {
        $this->havePath('{"one":{"two":{"three":4}}}', 'one/two/three')->shouldBe(true);
    }

    public function it_does_not_match_values()
    {
        $this->havePath('{"one":{"two":{"three":4}}}', 'one/two/three/4')->shouldBe(false);
    }

    public function it_matches_array_indexes()
    {
        $this->havePath('[1,[1,2,[1,2,3,4]]]', '1/2/3')->shouldBe(true);
    }

    public function it_respects_null_array_values()
    {
        $this->havePath('[null,[null,null,[null,null,null,null]]]', '1/2/3')->shouldBe(true);
    }

    public function it_matches_hash_keys_and_array_indexes()
    {
        $this->havePath('{"one":[1,2,{"three":4}]}', 'one/2/three')->shouldBe(true);
    }

    public function it_matches_hash_keys_with_given_base_path(MatcherOptions $options)
    {
        $options->getPath()->willReturn('one');
        $this->havePath('{"one":{"two":{"three":4}}}', 'two/three')->shouldBe(true);
    }
    //</editor-fold>

    // <editor-fold desc="haveSize spec">
    public function it_counts_array_entries()
    {
        $this->haveSize('[1,2,3]', 3)->shouldBe(true);
    }

    public function it_counts_null_array_entries()
    {
        $this->haveSize('[1,null,3]', 3)->shouldBe(true);
    }

    public function it_counts_hash_key_value_pairs()
    {
        $this->haveSize('{"one":1,"two":2,"three":3}', 3)->shouldBe(true);
    }

    public function it_counts_null_hash_values()
    {
        $this->haveSize('{"one":1,"two":null,"three":3}', 3)->shouldBe(true);
    }

    public function it_matches_size_at_a_path(MatcherOptions $options)
    {
        $options->getPath()->willReturn('one');
        $this->haveSize('{"one":[1,2,3]}', 3)->shouldBe(true);
    }
    //</editor-fold>

    // <editor-fold desc="haveType spec">
    public function it_matches_objects()
    {
        $this->haveType('{}', 'object')->shouldBe(true);
    }

    public function it_matches_arrays()
    {
        $this->haveType('[]', 'array')->shouldBe(true);
    }

    public function it_matches_type_at_a_path(MatcherOptions $options)
    {
        $options->getPath()->willReturn('root');
        $this->haveType('{"root":[]}', 'array')->shouldBe(true);
    }

    public function it_matches_strings(MatcherOptions $options)
    {
        $options->getPath()->willReturn('0');
        $this->haveType('["json_spec"]', 'string')->shouldBe(true);
    }

    public function it_matches_a_valid_JSON_value_yet_invalid_JSON_document()
    {
        $this->haveType('"json_spec"', 'string')->shouldBe(true);
    }

    public function it_matches_empty_strings()
    {
        $this->haveType('""', 'string')->shouldBe(true);
    }

    public function it_matches_integers()
    {
        $this->haveType('10', 'integer')->shouldBe(true);
    }

    public function it_matches_floats()
    {
        $this->haveType('10.0', 'float')->shouldBe(true);
        $this->haveType('1e+1', 'float')->shouldBe(true);
    }

    public function it_matches_booleans()
    {
        $this->haveType('true', 'boolean')->shouldBe(true);
        $this->haveType('false', 'boolean')->shouldBe(true);
    }
    //</editor-fold>

    // <editor-fold desc="includes spec">
    public function it_matches_included_array_elements()
    {
        $json = '["one",1,1.0,true,false,null]';
        $this->includes($json, '"one"')->shouldReturn(true);
        $this->includes($json, '1')->shouldReturn(true);
        $this->includes($json, '1.0')->shouldReturn(true);
        $this->includes($json, 'true')->shouldReturn(true);
        $this->includes($json, 'false')->shouldReturn(true);
        $this->includes($json, 'null')->shouldReturn(true);
    }

    public function it_matches_an_array_included_in_an_array()
    {
        $json = '[[1,2,3],[4,5,6]]';
        $this->includes($json, '[1, 2, 3]')->shouldReturn(true);
        $this->includes($json, '[4, 5, 6]')->shouldReturn(true);
    }

    public function it_matches_a_hash_included_in_an_array()
    {
        $json = '[{"one":1},{"two":2}]';
        $this->includes($json, '{"one":1}')->shouldReturn(true);
        $this->includes($json, '{"two":2}')->shouldReturn(true);
    }

    public function it_matches_included_hash_values()
    {
        $json = '{"string":"one","integer":1,"float":1.0,"true":true,"false":false,"null":null}';
        $this->includes($json, '"one"')->shouldReturn(true);
        $this->includes($json, '1')->shouldReturn(true);
        $this->includes($json, '1.0')->shouldReturn(true);
        $this->includes($json, 'true')->shouldReturn(true);
        $this->includes($json, 'false')->shouldReturn(true);
        $this->includes($json, 'null')->shouldReturn(true);
    }

    public function it_matches_a_hash_included_in_a_hash()
    {
        $json = '{"one":{"two":3},"four":{"five":6}}';
        $this->includes($json, '{"two":3}')->shouldReturn(true);
        $this->includes($json, '{"five":6}')->shouldReturn(true);
    }

    public function it_matches_an_array_included_in_a_hash()
    {
        $json = '{"one":[2,3],"four":[5,6]}';
        $this->includes($json, '[2,3]')->shouldReturn(true);
        $this->includes($json, '[5,6]')->shouldReturn(true);
    }

    public function it_matches_a_substring()
    {
        $json = '"json"';
        $this->includes($json, '"js"')->shouldReturn(true);
        $this->includes($json, '"json"')->shouldReturn(true);
    }

    public function it_matches_t_a_path(MatcherOptions $options)
    {
        $json = '{"one":{"two":[3,4]}}';
        $options->getPath()->willReturn('one');
        $this->includes($json, '[3,4]')->shouldReturn(true);
    }

    public function it_ignores_excluded_keys()
    {
        $json = '[{"id":1,"two":3}]';
        $this->includes($json, '{"two":3}')->shouldReturn(true);
    }
    // </editor-fold>
}
