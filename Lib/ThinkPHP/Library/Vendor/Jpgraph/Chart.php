<?php
class Chart{
	public $datay = array();
	public $datay1 = array();
	public $datay2 = array();
	public $legend = array();
	public $legend1;
	public $legend2;
	public $labels = array();
	public $title;
	public $width = 1500;
	public $height = 410;
	public $show_value = 0;
	public $radius = 200;
	public $color = array('black','red','blue','purple','orange');
	public $format = '%0.2f';
	public $format_callback = 0;
	public $xaxis_font = 10;
	public $fillcolor = array('blue@0.4','orange@0.4','brown@0.4','green@0.4');
	public $piepos = array();
	public $size;
	
	public function createSingleline(){
		require (dirname(__FILE__)."/Jpgraph/jpgraph.php");
		require (dirname(__FILE__)."/Jpgraph/jpgraph_line.php");
		foreach ($this->labels as $k => $v) {
			$this->labels[$k] = mb_convert_encoding($v, 'gbk', 'utf-8');
		}
		$graph = new Graph($this->width,$this->height);
		$graph->SetMarginColor('white');
		$graph->img->SetMargin(40,40,40,80);
		$graph->img->SetAntiAliasing();
		$graph->SetScale("textlin");	
		$graph->SetShadow();
		$graph->title->Set(mb_convert_encoding($this->title, 'gbk', 'utf-8'));
		$graph->title->SetFont(FF_SIMSUN,FS_BOLD,14);
		$graph->SetFrame(false,'white');
		$graph->xaxis->SetFont(FF_SIMSUN,FS_NORMAL,8);
		$graph->xaxis->SetTickLabels($this->labels);
		$graph->xaxis->SetColor('orange','black');
		$graph->yaxis->SetColor('orange','black');	
		$p = new LinePlot($this->datay1);
		$p->mark->SetType(MARK_FILLEDCIRCLE);
		$p->mark->SetFillColor('yellow');
		$p->mark->SetColor('blue');
		$p->mark->SetWidth(3);
		$p->SetColor("blue");
		$p->SetCenter();
		if($this->legend1){
			$p->SetLegend(mb_convert_encoding($this->legend1,'gbk','utf-8'));
		}
		if($this->show_value){
			$p->value->show();
			$p->value->SetFormat($this->format);
			if($this->format_callback){
				$p->value->SetFormatCallback('cb_format_percentage');
			}
			$p->value->SetColor('red');
		}
		$graph->Add($p);
		$graph->legend->SetLayout(LEGEND_HOR);
		$graph->legend->Pos(0.5,0.95,"center","bottom");
		$graph->Stroke();						
	}
	
	public function createDoubleline(){
		require (dirname(__FILE__)."/Jpgraph/jpgraph.php");
		require (dirname(__FILE__)."/Jpgraph/jpgraph_line.php");		
		
		foreach ($this->labels as $k => $v) {
		   $this->labels[$k] = mb_convert_encoding($v, 'gbk', 'utf-8');
		}
		//$theme_class = new UniversalTheme();
		$graph = new Graph($this->width,$this->height);
		$graph->SetMarginColor('white');
		$graph->img->SetMargin(40,40,40,80);    
		$graph->img->SetAntiAliasing();
		$graph->SetScale("textlin");
		//$graph->SetBackgroundGradient('#EBEBEB','navy:0.5',GRAD_HOR,BGRAD_PLOT);
					
		$graph->SetShadow();
		$graph->title->Set(mb_convert_encoding($this->title, 'gbk', 'utf-8'));
		$graph->title->SetFont(FF_SIMSUN,FS_BOLD,14);
		$graph->SetFrame(false,'white');
		$graph->xaxis->SetFont(FF_SIMSUN,FS_NORMAL,10);
		$graph->xaxis->SetTickLabels($this->labels);
		//$graph->xaxis->SetLabelAngle(0);
		//$graph->xgrid->SetLineStyle("solid");
		$graph->xaxis->SetColor('orange','black');
		$graph->yaxis->SetColor('orange','black');
						
		$p1 = new LinePlot($this->datay1);
		$p1->mark->SetType(MARK_FILLEDCIRCLE);
		$p1->mark->SetWidth(3);
		$p1->mark->SetFillColor('white');
		$p1->mark->SetColor('#462300');
		if($this->show_value){
			$p1->value->Show();
		//$p1->value->SetColor('red');
			$p1->value->SetFormat($this->format);
		}
		$p1->SetColor("#462300");
		$p1->SetLegend($this->legend1);
		$p1->SetCenter();
		$graph->Add($p1);
		
		$p2 = new LinePlot($this->datay2);
		$p2->mark->SetType(MARK_FILLEDCIRCLE);
		$p2->mark->SetFillColor('yellow');
		$p2->mark->SetColor('#009926');
		$p2->mark->SetWidth(3);
		if($this->show_value){
			$p2->value->Show();
			$p2->value->SetFormat($this->format);
		//$p2->value->SetColor('yellow');
		}
		$p2->SetColor("#009926");
		$p2->SetCenter();
		$p2->SetLegend($this->legend2);
		$graph->Add($p2);
		$graph->legend->SetFont(FF_SIMSUN,FS_NORMAL,10);
		$graph->legend->SetLayout(LEGEND_HOR);
		$graph->legend->Pos(0.5,0.95,"center","bottom");			
		$graph->Stroke(); 	
	}
	
	/*
	 * 单柱状图
	 * */
	public function createSinglecolumn(){
		require (dirname(__FILE__)."/Jpgraph/jpgraph.php");
		require (dirname(__FILE__)."/Jpgraph/jpgraph_bar.php");	

		foreach ($this->labels as $k => $v) {
			$this->labels[$k] = mb_convert_encoding($v, 'gbk', 'utf-8');
		}		
		$graph = new Graph($this->width,$this->height);
		$graph->img->SetMargin(60,20,35,75);
		$graph->SetScale("textlin");
		$graph->SetFrame(false,'white');
		$graph->SetMarginColor("lightblue:1.1");
		$graph->SetShadow();
		$graph->title->Set(mb_convert_encoding($this->title, 'gb2312', 'utf-8'));
		$graph->title->SetFont(FF_SIMSUN,FS_BOLD,14);
		$graph->title->SetMargin(6);
		$graph->title->SetColor("darkred");
		
		$graph->xaxis->SetFont(FF_SIMSUN,FS_NORMAL,10);
		$graph->yaxis->SetFont(FF_FONT2,FS_NORMAL,10);
		
		$graph->yscale->ticks->SupressZeroLabel(false);
		
		$graph->xaxis->SetTickLabels($this->labels);
		//$graph->xaxis->SetLabelAngle(50);
		
		$bplot = new BarPlot($this->datay1);
		$bplot->SetWidth(0.2);
		$bplot->value->Show();
		$bplot->value->SetFormat("%0d");
		$bplot->SetFillGradient("navy:0.9","navy:1.85",GRAD_LEFT_REFLECTION);
		$bplot->SetColor("white");
		$graph->Add($bplot);
		
		$graph->Stroke();			
	}
	
	/*
	 * 多柱状图
	 * */
	public function createMultcolumn(){
		require (dirname(__FILE__)."/Jpgraph/jpgraph.php");
		require (dirname(__FILE__)."/Jpgraph/jpgraph_bar.php");
		
		foreach ($this->labels as $k => $v) {
			$this->labels[$k] = mb_convert_encoding($v, 'gbk', 'utf-8');
		}
		$graph = new Graph(1250,420,'auto');	
		$graph->SetScale("textlin");
		$graph->img->SetMargin(40,80,30,40);
		$graph->SetFrame(false,'white');
		$graph->legend->Pos(0.02,0.15);
		
		$graph->legend->SetShadow('darkgray@0.6');
		$graph->legend->SetFillColor('lightblue@0.6');
		
		// Set axis titles and fonts
/* 		$graph->xaxis->title->Set('aaa');
		$graph->xaxis->title->SetFont(FF_FONT2,FS_NORMAL,12);
		$graph->xaxis->title->SetColor('black'); */
		$graph->xaxis->SetTickLabels($this->labels);
		$graph->xaxis->SetFont(FF_SIMSUN,FS_NORMAL,12);
		$graph->xaxis->SetColor('black');
		
		$graph->yaxis->SetFont(FF_FONT2,FS_NORMAL,12);
		$graph->yaxis->SetColor('black');
		
		$graph->ygrid->Show(false);
		$graph->ygrid->SetColor('white@0.5');
		
		$graph->title->Set(mb_convert_encoding($this->title, 'gbk', 'utf-8'));
		$graph->title->SetMargin(3);
		$graph->title->SetFont(FF_SIMSUN,FS_BOLD,14);
		$graph->legend->SetFont(FF_SIMSUN,FS_NORMAL,12);
		$bplot = array();
		foreach ($this->datay as $key=>$val){
			${'p'.$key} = new BarPlot($val);
			$bplot[] = ${'p'.$key};
			${'p'.$key}->SetFillColor($this->fillcolor[$key]);
			${'p'.$key}->value->SetFormatCallback("cb_format_percentage");
			${'p'.$key}->value->show();
			${'p'.$key}->SetLegend(mb_convert_encoding($this->legend[$key],'gbk','utf-8'));
			${'p'.$key}->SetShadow('black@0.4');
		}
		$gbarplot = new GroupBarPlot($bplot);		
		$gbarplot->SetWidth(0.2);
		$graph->Add($gbarplot);	
		$graph->Stroke();
	}
	
	/*
	 * 多曲线图
	 * */
	public function createMultline(){
		require (dirname(__FILE__)."/Jpgraph/jpgraph.php");
		require (dirname(__FILE__)."/Jpgraph/jpgraph_line.php");	
		foreach ($this->labels as $k => $v) {
			$this->labels[$k] = mb_convert_encoding($v, 'gbk', 'utf-8');
		}
		$graph = new Graph($this->width,$this->height);
		$graph->SetMarginColor('white');
		$graph->img->SetMargin(40,40,40,80);
		$graph->img->SetAntiAliasing();
		$graph->SetScale("textlin");	
		$graph->SetShadow();
		$graph->title->Set(mb_convert_encoding($this->title, 'gbk', 'utf-8'));
		$graph->title->SetFont(FF_SIMSUN,FS_BOLD,14);
		$graph->SetFrame(false,'white');
		$graph->xaxis->SetFont(FF_SIMSUN,FS_NORMAL,$this->xaxis_font);
		$graph->xaxis->SetTickLabels($this->labels);	
		$graph->xaxis->SetColor('orange','black');
		$graph->yaxis->SetColor('orange','black');
		
		foreach ($this->datay as $key=>$val){
			${'p'.$key} = new LinePlot($val);
/* 			${'p'.$key}->mark->SetType(MARK_FILLEDCIRCLE);
			${'p'.$key}->mark->SetFillColor($this->color[$key]);
			${'p'.$key}->mark->SetColor($this->color[$key]);
			${'p'.$key}->mark->SetWidth(3); */
			${'p'.$key}->SetColor($this->color[$key]);
			${'p'.$key}->SetCenter();
			${'p'.$key}->SetStyle(1);
			${'p'.$key}->SetLegend(mb_convert_encoding($this->legend[$key], 'gbk', 'utf-8'));
			
			if($this->show_value){
				${'p'.$key}->value->show();		
				if($this->format_callback){
					${'p'.$key}->value->SetFormatCallback('cb_format_percentage');
				}else{
					${'p'.$key}->value->SetFormat($this->format[$key]);
				}
				${'p'.$key}->value->SetColor($this->color[$key]); 
			}		
				
			$graph->Add(${'p'.$key});			
		}				
		$graph->legend->SetFont(FF_SIMSUN,FS_NORMAL,10);
		$graph->legend->SetLayout(LEGEND_HOR);
		$graph->legend->Pos(0.5,0.95,"center","bottom");
		$graph->Stroke();		
	}
	
	/*
	 * 单个饼状图
	 * */
	public function createSinglepie(){
		require (dirname(__FILE__)."/Jpgraph/jpgraph.php");
		require (dirname(__FILE__)."/Jpgraph/jpgraph_pie.php");		
		require (dirname(__FILE__)."/Jpgraph/jpgraph_pie3d.php");

		$graph = new PieGraph($this->width,$this->height);
		$graph->SetShadow();
		$graph->SetFrame(false,'white');
		
		// Set A title for the plot
		$graph->title->Set(mb_convert_encoding($this->title, 'gb2312', 'utf-8'));
		$graph->title->SetFont(FF_SIMSUN,FS_BOLD,14);
		$graph->title->SetColor("darkblue");
		$graph->legend->Pos(0.15,0.1);
		$graph->legend->SetFont(FF_SIMSUN,FS_BOLD,14);
		
		//$graph->Labels->SetFont(FF_SIMSUN,FS_BOLD,21);
		// Create 3D pie plot
		$p = new PiePlot3d($this->datay1);
		$p->SetTheme("sand");
		$p->SetCenter(0.5);
		$p->SetSize($this->radius);
		// Adjust projection angle
		$p->SetAngle(30);
		
		$p->SetStartAngle(35);
		
		$p->ExplodeSlice(3);
		
		$p->value->SetFont(FF_SIMSUN,FS_BOLD,11);
		$p->value->SetColor('black');
		$p->value->SetFormat("%.2f%%");
		$p->SetLabels($this->labels);
		$p->SetLabelPos(1);
		$p->SetLegends($this->legend1);
		
		$graph->Add($p);
		$graph->Stroke();			
	}
	
	/*
	 * 多个饼状图
	 * */
	public function createMultpie(){
		require (dirname(__FILE__)."/Jpgraph/jpgraph.php");
		require (dirname(__FILE__)."/Jpgraph/jpgraph_pie.php");		
		require (dirname(__FILE__)."/Jpgraph/jpgraph_pie3d.php");
		
		$n = count($this->piepos)/2;
		$graph = new PieGraph($this->width,$this->height,'auto');
		
		$graph->SetMargin(1,1,40,1);
		$graph->SetMarginColor('white');
		$graph->SetShadow(false);
		
		$graph->title->Set(mb_convert_encoding($this->title, 'gbk', 'utf-8'));
		$graph->title->SetFont(FF_SIMSUN,FS_BOLD,14);
		$graph->title->SetColor('black');
		
		$p = array();
		
		foreach($this->datay as $key => $datay_val){
			$p[$key] = new PiePlot3D($datay_val);
			$p[$key]->SetCenter($this->piepos[2*$key],$this->piepos[2*$key+1]);
			$p[$key]->title->Set(mb_convert_encoding($this->labels[$key],'gbk','utf-8'));
			$p[$key]->title->SetColor('black');
			$p[$key]->title->SetFont(FF_SIMSUN,FS_BOLD,12);
			$p[$key]->value->SetFont(FF_SIMSUN,FS_BOLD,11);
			$p[$key]->value->SetColor('black');
			$p[$key]->value->Show();
			$p[$key]->value->SetFormat($this->format);
			$p[$key]->SetLabels($this->legend);
			$p[$key]->SetLabelPos(0.62);
			$p[$key]->SetSize($this->size);
			$p[$key]->SetEdge(false);
			$p[$key]->ExplodeSlice(3);		
			$graph->Add($p[$key]);
		}
/* 		$p[0]->SetLegends($this->legend);
		$graph->legend->Pos(0.01,0.35);
		$graph->legend->SetShadow(true);
		$graph->legend->SetFont(FF_SIMSUN,FS_BOLD,12); */	
		$graph->Stroke();		
	}
}