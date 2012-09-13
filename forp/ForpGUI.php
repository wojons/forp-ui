<?php
interface IForpPrinter {
	public function render();
}

abstract class ForpPrinterAbstract {

	protected $GUIManager;

	public function setGUIManager(ForpGUI $GUIManager)
    {
		$this->GUIManager = $GUIManager;
	}

	public function getGUIManager()
    {
		return $this->GUIManager;
	}
}

class ForpGUI {

	protected $printer;
	protected $stack = array();

	public function __construct(IForpPrinter $printer = null)
	{
		$this->printer = (null === $printer) ?
			new ForpDefaultPrinter() : $printer;
		$this->printer->setGUIManager($this);
	}

	public function setStack($stack)
	{
		$this->stack = $stack;
	}

	public function getStack()
	{
		return $this->stack;
	}

	public function render()
	{
		$this->printer->render();
	}
}

class ForpDefaultPrinter
	extends ForpPrinterAbstract
	implements IForpPrinter {
        public function render()
        {
            foreach($this->getGUIManager()->getStack() as $entry) {
                printf("[time:%09.0f] [memory:%09d] ", $entry['usec'], $entry['bytes']);
                for ($j = 0; $j < $entry['level']; ++$j) {
                            if ($j == $entry['level'] - 1) printf(" └── ");
                            else printf(" |   ");
                    }

                    !empty($entry['class']) and printf("%s::", $entry['class']);
                printf("%s (%s)%s", $entry['function'], $entry['file'], PHP_EOL);
            }
        }
}

class ForpConsolePrinter
    extends ForpPrinterAbstract
    implements IForpPrinter {
        public function render()
        {
            forp_print();
        }
}

class ForpTreePrinter
	extends ForpPrinterAbstract
	implements IForpPrinter {
        public function render()
        {
            ?>
            <style>
            #forp {
                    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                    font-weight: 300;
                    padding:10px;
                    border-top:1px solid #777;
                    -moz-box-shadow: inset 0 5px 5px -5px rgba(0,0,0,.75);
                    -webkit-box-shadow: inset 0 5px 5px -5px rgba(0,0,0,.75);
                    box-shadow: inset 0 5px 5px -5px rgba(0,0,0,.75);
            }
            #forp table{width:100%}
            #forp td{padding:0px 10px}
            #forp td.r{text-align:right}
            #forp div.i{float:left;}
            </style>
            <?php
            $line = "<tr><td class='r'>%d</td><td class='r'>%d</td><td><div class='i' style='width:%d0px;'>&nbsp;</div>%s%s</td><td>%s</td></tr>";
            ?>
            <div id="forp">
            <table>
                    <tr><th>duration(&#181;s)</th><th>memory(o)</th><th>function</th><th>file</th></tr>
            <?php
            foreach($this->getGUIManager()->getStack() as $entry) {
                printf( $line,
                        $entry['usec'],
                        $entry['bytes'],
                        $entry['level'],
                        empty($entry['class']) ? '' : $entry['class'] . '::',
                        $entry['function'],
                        $entry['file']
                        );
            }
            ?>
            </table>
            </div>
            <?php
        }
}