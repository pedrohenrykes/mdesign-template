<?php

class PrescreverMedicacaoDetail extends TWindow
{
    private $form;

    public function __construct()
    {
        parent::__construct();
        parent::setTitle( "Prescrever Medicação" );
        parent::setSize( 0.600, 0.800 );

        $redstar = '<font color="red"><b>*</b></font>';

        $this->form = new BootstrapFormBuilder( "detail_prescricao_medicao" );
        $this->form->setFormTitle( "({$redstar}) campos obrigatórios" );
        $this->form->class = "tform";

        $id                 = new THidden("id");
        $bau_id             = new THidden("bau_id");
        $paciente_id        = new THidden( "paciente_id" );
        $paciente_nome      = new TEntry( "paciente_nome" );
        $profissional_id    = new THidden("profissional_id");
        $dataprescricao     = new TDateTime("dataprescricao");
        $principioativo_id  = new TDBCombo("principioativo_id","database","PrincipioAtivoRecord","id","nomeprincipioativo");
        $medicamento_id     = new TDBCombo("medicamento_id","database","MedicamentoRecord","id","nomemedicamento");
        $dosagem            = new TEntry("dosagem");
        $posologia          = new TEntry("posologia");
        $observacao         = new TEntry("observacao");

        /*
        $criteria1 = new TCriteria;
        $criteria1->add(new TFilter("tipoprofissional_id","=",1));
        $medicamento_id = new TDBCombo("medicoobito_id","database","ProfissionalRecord","id","nomeprofissional","nomeprofissional",$criteria1);
        */

        $fk = filter_input( INPUT_GET, "fk" );
        $did = filter_input( INPUT_GET, "did" );

        $bau_id->setValue ($fk);
        $paciente_id->setValue ($did);
        $profissional_id->setValue ('1');
        //$profissional_id  ALTERAR PARA O DA SESSSAAAAOOOOO

        try {

            TTransaction::open( "database" );

            $bau = new BauRecord( $fk );
            $paciente = new PacienteRecord( $did );

            if( isset( $bau ) && isset( $paciente ) ) {
                $paciente_id->setValue( $paciente->id );
                $paciente_nome->setValue( $paciente->nomepaciente );

            }

            TTransaction::close();

        } catch ( Exception $ex ) {

            $action = new TAction( [ "PacientesAtendimentoList", "onReload" ] );

            new TMessage( "error", "Ocorreu um erro ao carregar as dependência do formulário.", $action );

        }
        /*

        $id                      ->setSize( "38%" );
        $paciente_nome           ->setSize( "38%" );
        $dataobito               ->setSize( "38%" );
        $horaobito               ->setSize( "38%" );
        $declaracaoobitodata     ->setSize( "38%" );
        $declaracaoobitohora     ->setSize( "38%" );
        $destinoobito_id         ->setSize( "38%" );
        $declaracaoobitomedico_id->setSize( "38%" );
        */

        $principioativo_id  ->setDefaultOption( "..::SELECIONE::.." );
        $medicamento_id     ->setDefaultOption( "..::SELECIONE::.." );

        $dataprescricao     ->setMask( "dd/mm/yyyy" );
        $dataprescricao     ->setDatabaseMask("yyyy-mm-dd");

        $dataprescricao->setValue( date( "d/m/Y" ) );
        $dataprescricao     ->setEditable( false );
        $paciente_nome      ->setEditable( false );

        $principioativo_id->addValidation( TextFormat::set( "Princípio Ativo" ), new TRequiredValidator );
        $medicamento_id->addValidation( TextFormat::set( "Medicamento" ), new TRequiredValidator );
        $dosagem->addValidation( TextFormat::set( "Dosagem" ), new TRequiredValidator );
        $posologia->addValidation( TextFormat::set( "Posologia" ), new TRequiredValidator );

        $this->form->addFields( [ new TLabel( "Nome do Paciente:" ) ], [ $paciente_nome ] );
        $this->form->addFields( [ new TLabel( "Data Prescrição:" ) ], [ $dataprescricao ] );
        $this->form->addFields( [ new TLabel( "Princípio Ativo:{$redstar}" ) ], [ $principioativo_id ] );
        $this->form->addFields( [ new TLabel( "Medicamento:{$redstar}" ) ], [ $medicamento_id ] );
        $this->form->addFields( [ new TLabel( "Dosagem:{$redstar}" ) ], [ $dosagem ] );
        $this->form->addFields( [ new TLabel( "Posologia:{$redstar}" ) ], [ $posologia ] );
        $this->form->addFields( [ new TLabel( "Observação" ) ], [ $observacao ] );
        $this->form->addFields( [ $id, $paciente_id, $profissional_id, $bau_id ] );

        $onSave   = new TAction( [ $this, "onSave" ] );
        $onSave->setParameter( "fk", $fk );
        $onSave->setParameter( "did", $did );
        $onReload = new TAction( [ "AtendimentoDetail", "onReload" ] );
        $onReload->setParameter( "fk", $fk );
        $onReload->setParameter( "did", $did );

        $this->form->addAction( "Salvar", $onSave, "fa:floppy-o" );
        $this->form->addAction( "Voltar para Atendimento", $onReload, "fa:table blue" );

        $this->datagrid = new BootstrapDatagridWrapper( new CustomDataGrid() );
        $this->datagrid->datatable = "true";
        $this->datagrid->style = "width: 100%";
        $this->datagrid->setHeight( '100%' );

        $column_1 = new TDataGridColumn( "paciente_nome", "Paciente", "left" );
        $column_2 = new TDataGridColumn( "principio_nome", "Princípio Ativo", "left" );
        $column_3 = new TDataGridColumn( "medicamento_nome", "Medicamento", "left" );
        $column_4 = new TDataGridColumn( "dosagem", "Dosagem", "left" );
        $column_5 = new TDataGridColumn( "dataprescricao", "Data Prescrição", "left" );

        $this->datagrid->addColumn( $column_1 );
        $this->datagrid->addColumn( $column_2 );
        $this->datagrid->addColumn( $column_3 );
        $this->datagrid->addColumn( $column_4 );
        $this->datagrid->addColumn( $column_5 );

        $action_edit = new CustomDataGridAction( [ $this, "onEdit" ] );
        $action_edit->setButtonClass( "btn btn-default" );
        $action_edit->setLabel( "Editar" );
        $action_edit->setImage( "fa:pencil-square-o blue fa-lg" );
        $action_edit->setField( "id" );
        $action_edit->setParameter( "fk", $fk );
        $action_edit->setParameter( "did", $did );
        $this->datagrid->addAction( $action_edit );

        $action_del = new CustomDataGridAction( [ $this, "onDelete" ] );
        $action_del->setButtonClass( "btn btn-default" );
        $action_del->setLabel( "Deletar" );
        $action_del->setImage( "fa:trash-o red fa-lg" );
        $action_del->setField( "id" );
        $action_del->setParameter( "fk", $fk );
        $action_del->setParameter( "did", $did );
        $this->datagrid->addAction( $action_del );

        $this->datagrid->createModel();

        $this->pageNavigation = new TPageNavigation();
        $this->pageNavigation->setAction( new TAction( [ $this, "onReload" ] ) );
        $this->pageNavigation->setWidth( $this->datagrid->getWidth() );

        $container = new TVBox();

        $container->style = "width: 100%";
        $container->add( $this->form );
        $container->add( TPanelGroup::pack( NULL, $this->datagrid ) );
        $container->add( $this->pageNavigation );

        parent::add( $container );
    }

    public function onSave( $param = null )
    {

        try {

            $this->form->validate();

            $object = $this->form->getData( "BauUsoMedicacoesRecord" );

            TTransaction::open( "database" );

            unset($object->paciente_nome);
            //$object->situacao = "OBITO";
            $object->store();

            TTransaction::close();

            $action = new TAction( [ $this, "onReload" ] );
            $action->setParameters( $param );
            new TMessage( "info", "Medicação Prescrita com sucesso!", $action );

        } catch ( Exception $ex ) {

            TTransaction::rollback();

            // $this->form->setData( $object );

            new TMessage( "error", "Ocorreu um erro ao tentar salvar o registro!<br><br><br><br>" . $ex->getMessage() );

        }
    }
    public function onEdit( $param = null )
    {
        try {

            if ( isset( $param[ "key" ] ) ) {
                TTransaction::open( "database" );

                $object = new BauUsoMedicacoesRecord( $param[ "key" ] );

                $object->dataprescricao = TDate::date2br($object->dataprescricao);

                $this->onReload( $param );
                $this->form->setData( $object );
                TTransaction::close();

            }

        } catch ( Exception $ex ) {

            TTransaction::rollback();

            new TMessage( "error", "Ocorreu um erro ao tentar carregar o registro para edição!<br><br>" . $ex->getMessage() );

        }
    }

    public function onDelete( $param = null )
    {
        if( isset( $param[ "key" ] ) ) {

            $param = [
            "key" => $param[ "key" ],
            "fk"  => $param[ "fk" ],
            "did"  => $param[ "did" ]
            ];

            $action1 = new TAction( [ $this, "Delete" ] );
            $action2 = new TAction( [ $this, "onReload" ] );

            $action1->setParameters( $param );
            $action2->setParameters( $param );

            new TQuestion( "Deseja realmente apagar o registro?", $action1, $action2 );
        }
    }

    public function Delete( $param = null )
    {
        try {

            TTransaction::open( "database" );
            $object = new BauUsoMedicacoesRecord( $param[ "key" ] );
            $object->delete();
            TTransaction::close();

            $this->onReload( $param );

            new TMessage( "info", "O Registro foi apagado com sucesso!" );

        } catch ( Exception $ex ) {
            TTransaction::rollback();
            new TMessage( "error", $ex->getMessage() );

        }
    }

    public function onReload( $param )
    {
        try {

            TTransaction::open( "database" );
            $repository = new TRepository( "BauUsoMedicacoesRecord" );
            $properties = [ "order" => "dataprescricao", "direction" => "desc" ];
            $limit = 10;

            $criteria = new TCriteria();
            $criteria->setProperties( $properties );
            $criteria->setProperty( "limit", $limit );
            $criteria->add( new TFilter( "bau_id", "=", $param[ "fk" ] ) );

            $objects = $repository->load( $criteria, FALSE );

            if ( isset( $objects ) ) {

                $this->datagrid->clear();

                foreach ( $objects as $object ) {

                    $object->dataprescricao = TDate::date2br($object->dataprescricao);
                    $this->datagrid->addItem( $object );

                }

            }

            $criteria->resetProperties();
            $count = $repository->count( $criteria );

            $this->pageNavigation->setCount( $count );
            $this->pageNavigation->setProperties( $properties );
            $this->pageNavigation->setLimit( $limit );

            //$this->onReloadFrames( $param );

            TTransaction::close();

            $this->loaded = true;

        } catch ( Exception $ex ) {

            TTransaction::rollback();

            new TMessage( "error", $ex->getMessage() );
        }
    }

}
