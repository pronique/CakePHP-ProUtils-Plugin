<?php
App::uses('ProTemplateCompiler', 'ProUtils.Lib');

class ProTemplateCompilerTest extends CakeTestCase {
    
    protected $fixtures = array(
        'template1'=>"
            Hello World 
            Testing 1 2 3 4 5 6
            print_r( 'Testing' );
            Configure::write( 'Hello', 'World' );
        ",
        'template2'=>"
            An example template file ready to use.
            When compiled this template will contain the date on the next line.
            {\$month}/{\$day}/{\$year}
        ",
        'template3'=>"
            An example template file with an alternative tag format.
            When compiled this template will contain the date on the next line.
            %%month%% %%day%% %%year%%
        ",
        'template4'=>'{$color}{$color}{$color}{$color}{$color}
        {$month}/{$day}-{$day}-{$day}-{$day}-{$day}-{$day}-{$day}/{$year}'
    );
/**
 * testCompile
 *
 * @return void
 */
    public function testCompile() {
        $template = 'This is a {$template} with embedded {$variables}, to test I am including multiple {$variables}.';
        $TemplateCompiler = new ProTemplateCompiler( $template );
        $data = array( 
            'template'=>'_template_replace_value_',
            'variables'=>'_variable_replace_value_'
        );
        
        $result = $TemplateCompiler->compile( $data );

        $this->assertTrue(is_string($result));
        $this->assertContains('_template_replace_value_', $result);
        $this->assertContains('_variable_replace_value_', $result);
        $this->assertEqual($result, 'This is a _template_replace_value_ with embedded _variable_replace_value_, to test I am including multiple _variable_replace_value_.');
    }
    
/**
 * testCompileMultiDim
 *
 * @return void
 */
    public function testCompileMultiDim() {
        $template = 'Once upon a time, there was a {$adjectives.a-m.h} knight named {$characters.male},'
            . ' who, by his {$adjectives.n-z.v} gloom, saved the kingdom from the {$adjectives.a-m.h2}' 
            . ' dragon and {$verbs.verb1} the princess in the nick of time. He rode'
            . ' his {$adjectives.n-z.w} camel from the flat plain upon which the kingdom was' 
            . ' built, journeying into the unknown. He was bold and did not '
            . 'stop for {$verbs.verb2} the dragon would wear them all.';
            
        $TemplateCompiler = new ProTemplateCompiler( $template );
        
        $data = array( 
            'characters'=>array( 'male'=>'Sam' ),
            'verbs'=>array( 'verb1'=>'pinched', 'verb2'=>'fear' ),
            'adjectives'=>array( 
                'a-m'=>array( 'h'=>'happy', 'h2'=>'hungry' ),
                'n-z'=>array( 'v'=>'valiant', 'w'=>'wet' )
            )
        );
        
        $result = $TemplateCompiler->compile( $data );

        $this->assertTrue(is_string($result));
        $this->assertContains('the hungry dragon', $result);
        $this->assertContains('and pinched the princess', $result);
        $this->assertContains('knight named Sam,', $result);
        $this->assertContains('his valiant gloom', $result);
        $this->assertContains('his valiant gloom', $result);
        $this->assertNotContains( '${', $result );
        $this->assertNotContains( '}', $result );
    }
    
    
    function testSimpleCompile() {
        $tc = new ProTemplateCompiler( $this->fixtures['template2'] );

        $data = array(
            'month'=>date('M'),
            'day'=>date('j'),
            'year'=>date('Y')
        );
        
        $this->assertContains( date('M/j/Y' ), $tc->compile( $data ));
    }
     
    function testCompileAltTag() {
        //$tc = new ProTemplateCompiler( $this->fixtures['template2'] );
   
        //$data = array(
        //    'month'=>date('m'),
        //   'day'=>date('j'),
        //    'year'=>date('Y')    
        //);
        
        //echo $tc->compile( $data  );
    }
    
    function testPrepareAndCompile() {
        $rules = array(
            'print_r'=>array(
                'match'=>"/((print_r)\(\s*')(.*)('\s*\);)/"
            ),
            'rule2'=>array(
                'match'=>"/(Configure::write\s*\(\s*'(Hello)'\s*,\s*')(.*)('\s*\);)/"
            )
        );
        
        $tc = new ProTemplateCompiler( $this->fixtures['template1'], $rules );

        $data = array(
            'print_r'=>'ItWorked!',
            'Hello'=>'HelloHelloHelloHelloWorldWorldWorldWorld'
        );
        
        $results = $tc->compile( $data  );
        $this->assertContains( 'ItWorked!', $results );
        $this->assertContains( 'HelloHelloHelloHelloWorldWorldWorldWorld', $results );
    }
    
    function testReplaceMultipleInstances() {

        
        $tc = new ProTemplateCompiler( $this->fixtures['template4'] );

        $data = array(
            'color'=>'red',
            'month'=>date('M'),
            'day'=>date('j'),
            'year'=>date('Y')
        );
        
        $results = $tc->compile( $data  );
        $this->assertContains( 'redredredredred', $results );
        $this->assertContains( date('M/j-j-j-j-j-j-j/Y'), $results );  
    }

}