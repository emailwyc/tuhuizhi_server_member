runtime! debian.vim
if has("syntax")
  syntax on
endif
set showcmd             " Show (partial) command in status line.
"set showmatch          " Show matching brackets.
"set ignorecase         " Do case insensitive matching
"set smartcase          " Do smart case matching
set incsearch           " Incremental search
set autowrite           " Automatically save before commands like :next and :make
set cindent
set softtabstop=4
set shiftwidth=4
set tabstop=4
set hlsearch
filetype indent on
map <C-n> :nohl<cr>
set nu
let g:neocomplcache_enable_at_startup = 1
func SetEncodingUTF8()
     :set enc=utf-8
     :set tenc=utf-8
     :e
endfunc
map  <F11>      :call SetEncodingUTF8() <CR>
imap <F11>      <ESC>:call SetEncodingUTF8() <CR>
:inoremap ( ()<ESC>i
		:inoremap ) <c-r>=ClosePair(')')<CR>
:inoremap { {}<ESC>i
	:inoremap } <c-r>=ClosePair('}')<CR>
	:inoremap [ []<ESC>i
	:inoremap ] <c-r>=ClosePair(']')<CR>
	:inoremap < <><ESC>i
	:inoremap > <c-r>=ClosePair('>')<CR>

	function ClosePair(char)
	     if getline('.')[col('.') - 1] == a:char
	          return "\<Right>"
		       else
		            return a:char
			           endif
				   endf

				   function! PhpCheckSyntax()
	" Check php syntax
	setlocal makeprg=\php\ -l\ -n\ -d\ html_errors=off\ %
	 " Set shellpipe setlocal shellpipe=>
	  " Use error format for parsing PHP error output
	  setlocal errorformat=%m\ in\ %f\ on\ line\ %l
	  make %endfunction
	  " Perform :PhpCheckSyntax()map <F6> :call PhpCheckSyntax()<CR>
	  " imap <F6> <ESC>:call PhpCheckSyntax()<CR>
	  endf
	  autocmd BufWritePost *.php :call PhpCheckSyntax()
:set fileformat=unix
:set fileencodings=ucs-bom,utf-8,cp936,gb18030,big5,euc-jp,euc-kr,latin1
