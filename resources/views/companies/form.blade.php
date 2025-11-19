<div class="mb-3">
    <label class="form-label">Company Name <span class="text-danger">*</span></label>
    <input type="text" name="name" value="{{ old('name', $company->name ?? '') }}" class="form-control" required>
    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" value="{{ old('email', $company->email ?? '') }}" class="form-control">
    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Phone</label>
    <input type="text" name="phone" value="{{ old('phone', $company->phone ?? '') }}" class="form-control">
    @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Address</label>
    <textarea name="address" class="form-control" rows="2">{{ old('address', $company->address ?? '') }}</textarea>
    @error('address') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select">
        <option value="active" {{ (old('status', $company->status ?? '') == 'active') ? 'selected' : '' }}>Active</option>
        <option value="inactive" {{ (old('status', $company->status ?? '') == 'inactive') ? 'selected' : '' }}>Inactive
        </option>
    </select>
    @error('status') <small class="text-danger">{{ $message }}</small> @enderror
</div>