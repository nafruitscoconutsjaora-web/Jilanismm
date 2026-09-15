<?php
/** @var array $admin */
/** @var array $categories */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Service Categories</h1>
            <p class="text-sm text-zinc-500 mt-1">Organize social media services into structured categories and display hierarchies.</p>
        </div>
        <button type="button" onclick="openCreateModal()" class="inline-flex items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add New Category
        </button>
    </div>

    <!-- Categories Table -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                        <th class="py-3 px-6">ID</th>
                        <th class="py-3 px-4">Category Name</th>
                        <th class="py-3 px-4">Icon / Slug</th>
                        <th class="py-3 px-4 text-center">Sort Order</th>
                        <th class="py-3 px-4 text-center">Services</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-zinc-400">No categories created yet. Click "Add New Category" above.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                <td class="py-3.5 px-6 font-mono font-bold text-zinc-900">#<?= (int)$cat['id'] ?></td>
                                <td class="py-3.5 px-4 font-bold text-zinc-900 text-sm">
                                    <?= e($cat['name']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-zinc-500 font-mono">
                                    <?= e($cat['icon'] ?? 'tag') ?>
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono text-zinc-700">
                                    <?= (int)($cat['sort_order'] ?? 0) ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-zinc-100 text-zinc-800 font-bold text-[11px]">
                                        <?= (int)($cat['service_count'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border <?= $cat['status'] === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-zinc-100 text-zinc-600 border-zinc-200' ?>">
                                        <?= ucfirst($cat['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-right whitespace-nowrap space-x-2">
                                    <!-- Edit Button -->
                                    <button type="button" onclick='openEditModal(<?= json_encode($cat, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-2.5 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold rounded-lg transition-colors">
                                        Edit
                                    </button>

                                    <!-- Status Toggle Form -->
                                    <form action="/admin/categories/status/<?= (int)$cat['id'] ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition-colors <?= $cat['status'] === 'active' ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' ?>">
                                            <?= $cat['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>

                                    <!-- Delete Form -->
                                    <form action="/admin/categories/delete/<?= (int)$cat['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Delete category &quot;<?= e($cat['name']) ?>&quot;? This cannot be undone.')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="px-2.5 py-1 border border-rose-200 text-rose-700 hover:bg-rose-50 text-xs font-semibold rounded-lg transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Category Modal -->
<div id="createCategoryModal" class="fixed inset-0 z-50 hidden bg-zinc-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-zinc-200">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
            <h3 class="text-base font-bold text-zinc-900">Create New Category</h3>
            <button type="button" onclick="closeCreateModal()" class="text-zinc-400 hover:text-zinc-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="/admin/categories/create" method="POST" class="space-y-4 mt-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Category Name</label>
                <input type="text" name="name" required placeholder="e.g. Instagram Followers" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2.5 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Icon Identifier</label>
                <input type="text" name="icon" value="tag" placeholder="e.g. instagram, youtube, tag" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2.5 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="0" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2.5 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Status</label>
                    <select name="status" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2.5 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-zinc-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 rounded-xl text-xs font-medium text-zinc-600 hover:bg-zinc-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white">Create Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="fixed inset-0 z-50 hidden bg-zinc-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-zinc-200">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
            <h3 class="text-base font-bold text-zinc-900">Edit Category</h3>
            <button type="button" onclick="closeEditModal()" class="text-zinc-400 hover:text-zinc-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="editCategoryForm" action="" method="POST" class="space-y-4 mt-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Category Name</label>
                <input type="text" id="editName" name="name" required class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2.5 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Icon Identifier</label>
                <input type="text" id="editIcon" name="icon" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2.5 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Sort Order</label>
                    <input type="number" id="editSortOrder" name="sort_order" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2.5 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Status</label>
                    <select id="editStatus" name="status" class="w-full text-xs rounded-xl border border-zinc-300 px-3 py-2.5 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-zinc-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl text-xs font-medium text-zinc-600 hover:bg-zinc-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('createCategoryModal').classList.remove('hidden');
}
function closeCreateModal() {
    document.getElementById('createCategoryModal').classList.add('hidden');
}
function openEditModal(cat) {
    document.getElementById('editCategoryForm').action = '/admin/categories/edit/' + cat.id;
    document.getElementById('editName').value = cat.name;
    document.getElementById('editIcon').value = cat.icon || 'tag';
    document.getElementById('editSortOrder').value = cat.sort_order || 0;
    document.getElementById('editStatus').value = cat.status || 'active';
    document.getElementById('editCategoryModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editCategoryModal').classList.add('hidden');
}
</script>
