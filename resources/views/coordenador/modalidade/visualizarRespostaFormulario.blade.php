@extends('coordenador.detalhesEvento')

@section('menu')

    <div id="divListarCriterio" class="comissao">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-detalhes"> <strong> {{$trabalho->titulo}}</strong> <br>
                    Modalidade: <strong> {{$modalidade->nome}}</strong><br>
                    Revisor: <strong> {{$revisorUser->name}}</strong><br>
                </h3>
            </div>
        </div>
    </div>
    @if(session('message'))
        <div class="row">
            <div class="col-md-12" style="margin-top: 5px;">
                <div class="alert alert-success">
                    <p>{{session('message')}}</p>
                </div>
            </div>
        </div>
    @endif
    <form id="editarRespostas" action="{{route('revisor.editar.respostas')}}" method="post">
        @csrf
        <input type="hidden" name="trabalho_id" value="{{$trabalho->id}}">
        @foreach ($modalidade->forms as $form)
            <div class="card" style="width: 48rem;">
                <div class="card-body">
                <h5 class="card-title">{{$form->titulo}}</h5>

                <p class="card-text">

                    @foreach ($form->perguntas as $pergunta)
                        <div class="card">
                            <div class="card-body">
                                <p><strong>{{$pergunta->pergunta}}</strong> <span><small style="float: right">Pergunta visível para o autor? <input type="checkbox" name="pergunta_checkBox[]" value="{{$pergunta->id}}" {{  ($pergunta->visibilidade == true ? ' checked' : '') }} disabled></small></span>
                                </p>

                                @if($pergunta->respostas->first()->opcoes->count())
                                    Resposta com Multipla escolha:
                                @elseif($pergunta->respostas->first()->paragrafo->count() )
                                    @forelse ($pergunta->respostas as $resposta)
                                        @if($resposta->revisor != null || $resposta->trabalho != null)
                                            @if($resposta->revisor->user_id == $revisorUser->id && $resposta->trabalho->id == $trabalho->id)

                                                <p class="card-text">
                                                    <input type="hidden" name="pergunta_id[]" value="{{$pergunta->id}}">
                                                    <input type="hidden" name="resposta_paragrafo_id[]" value="{{$resposta->paragrafo->id}}">
                                                    <textarea id="resposta{{$resposta->paragrafo->id}}" type="text" class="form-control @error('resposta'.$resposta->paragrafo->id) is-invalid @enderror" name="resposta{{$resposta->paragrafo->id}}" required>@if(old('resposta'.$resposta->paragrafo->id)!=null){{old('resposta'.$resposta->paragrafo->id)}}@else{{($resposta->paragrafo->resposta)}}@endif</textarea>
                                                </p>
                                                <div class="col-form-label text-md-left">
                                                    <small>Resposta visível para o autor? (selecione se sim) </small><input type="checkbox" name="paragrafo_checkBox[]" value="{{$resposta->paragrafo->id}}" {{  ($resposta->paragrafo->visibilidade == true ? ' checked' : '') }} {{$pergunta->visibilidade == true ? '' : 'disabled' }}>
                                                </div>
                                            @endif
                                        @endif
                                        @empty
                                        <p>Sem respostas</p>
                                    @endforelse
                                @endif
                            </div>
                        </div>

                    @endforeach
                    <div class="col-form-label text-md-left">
                        <small>Selecionar todas as respostas </small><input id="selecionarTodas" type="checkbox" onclick="select_all()">
                    </div>

                </p>
                </div>
            </div>

        @endforeach
        <div class="row justify-content-left">
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary" id="submeterFormBotao">
                    {{ __('Editar parecer') }}
                </button>
            </div>
        </div>
    </form>
    @if ($trabalho->arquivoAvaliacao()->first() !== null)
        <div class="d-flex justify-content-left">
            <a class="btn btn-primary" href="{{route('downloadAvaliacao', ['trabalhoId' => $trabalho->id, 'revisorUserId' => $revisorUser->id])}}">
                <div class="btn-group">
                    <img src="{{asset('img/icons/file-download-solid.svg')}}" style="width:15px">
                    <h6 style="margin-left: 5px; margin-top:1px; margin-bottom: 1px;">Baixar trabalho corrigido</h6>
                </div>
            </a>
            @can('isCoordenadorOrComissao', $evento)
                <div class="col-md-4" style="padding-ridht:0">
                    @if ($trabalho->status == 'rascunho')
                        <a href="{{ route('trabalho.status', [$trabalho->id, 'avaliado']) }}" class="btn btn-secondary">
                            Encaminhar parecer ao autor
                        </a>
                    @elseif ($trabalho->status == 'avaliado')
                        <a href="{{ route('trabalho.status', [$trabalho->id, 'rascunho']) }}" class="btn btn-secondary">
                            Desfazer encaminhamento do parecer
                        </a>
                    @endif
                </div>
            @endcan
        </div>
    @else
        <div class="d-flex justify-content-left">
            <div>
                <a class="btn btn-primary">
                    <div class="btn-group">
                        <img src="{{asset('img/icons/file-download-solid.svg')}}" style="width:15px">
                        <h6 style="margin-left: 5px; margin-top:1px; margin-bottom: 1px; color:#fff">Baixar trabalho corrigido</h6>
                    </div>
                </a>
            </div>
            @can('isCoordenadorOrComissao', $evento)
                <div class="col-md-4" style="padding-ridht:0">
                    @if ($trabalho->status == 'rascunho')
                        <a href="{{ route('trabalho.status', [$trabalho->id, 'avaliado']) }}" class="btn btn-secondary">
                            Encaminhar parecer ao autor
                        </a>
                    @elseif ($trabalho->status == 'avaliado')
                        <a href="{{ route('trabalho.status', [$trabalho->id, 'rascunho']) }}" class="btn btn-secondary">
                            Desfazer encaminhamento do parecer
                        </a>
                    @endif
                </div>
            @endcan
        </div>
        <div style="margin-left:10px">
            <h6 style="color: red">A correção não foi <br> enviada pelo parecerista.</h6>
        </div>
    @endif


@endsection

@section('javascript')
    <script type="text/javascript">
        var respostas;

        function select_all() {
            respostas = document.getElementsByName('paragrafo_checkBox[]');
            if (document.getElementById('selecionarTodas').checked)
            {
                for (i = 0; i < respostas.length; i++) {
                    if(!respostas[i].checked & !respostas[i].disabled){
                        respostas[i].checked = true;
                    }
                }
            } else {
                for (i = 0; i < respostas.length; i++) {
                    if(respostas[i].checked){
                        respostas[i].checked = false;
                    }
                }
            }
        }

    </script>
@endsection