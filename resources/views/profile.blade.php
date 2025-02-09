<div class="modal fade" wire:ignore.self id="profile" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title me-3 fw-bold text-danger">Profile</h3>
                <button class="btn btn-danger rounded-circle me-3 mt-1" id="enable-profile" onclick="enableForm(this, 'disable-profile', 'profile-form')"><i class="fa fa-pencil fa-sm"></i></button>
                <button class="btn btn-danger rounded-circle me-3 mt-1 d-none" id="disable-profile" onclick="disableForm(this, 'enable-profile', 'profile-form')"><i class="fa-regular fa-xmark fa-sm"></i></button>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ url('profile/update/'.Auth::user()->line_id ) }}" method="post" id="profile-form" onsubmit="submitForm(this, event)">
                    {{ method_field('PUT') }}
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">Nama Line</span>
                            <input type="text" class="form-control fs-6" id="full-name" name="full_name" value="{{ Auth::user()->FullName }}" disabled>
                        </div>
                    </div>
                    {{-- <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">Username</span>
                            <input type="text" class="form-control fs-6" id="username" value="{{ Auth::user()->username }}" disabled>
                        </div>
                    </div> --}}
                    <div class="mb-3">
                        <div class="row">
                            <div class="col">
                                <input type="password" class="form-control fs-6" id="password" name="password" placeholder="Password Baru" disabled>
                            </div>
                            <div class="col">
                                <input type="password" class="form-control fs-6" id="password-confirm" name="password_confirmation" placeholder="Konfirmasi Password" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-danger w-100 d-none">Ubah Profil</button>
                    </div>
                </form>
                {{-- <div class="mt-5">
                    @livewire('profile-content')
                </div> --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
