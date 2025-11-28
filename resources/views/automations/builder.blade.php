<x-layouts.app>
    @php
        $isEdit = ! is_null($automation);
        $initialName    = $isEdit ? $automation->name : '';
        $initialTrigger = $isEdit ? $automation->trigger : '';
        $initialFlow    = $isEdit ? json_encode($automation->flow_definition ?? [], JSON_UNESCAPED_UNICODE) : '{}';
    @endphp

    <div x-data="automationBuilder({{ json_encode($initialName) }}, {{ json_encode($initialTrigger) }}, {!! $initialFlow !!})"
         x-init="init()"
         class="flex h-[calc(100vh-120px)] bg-gray-50 border rounded-lg overflow-hidden">

        <!-- Sidebar blocks -->
        <div class="w-64 border-r bg-white">
            <div class="px-4 py-3 border-b">
                <h1 class="text-sm font-semibold">Blocks</h1>
                <p class="text-[11px] text-gray-500">Drag to build your WhatsApp flow.</p>
            </div>

            <div class="px-4 pt-3">
                <h2 class="text-[11px] font-semibold text-gray-500 mb-1">Messages</h2>
                <template x-for="block in messageBlocks" :key="block.type">
                    <div class="px-3 py-2 mb-2 text-xs bg-gray-50 border rounded cursor-move hover:bg-gray-100"
                         draggable="true"
                         @dragstart="onPaletteDragStart($event, block)">
                        <span x-text="block.label"></span>
                    </div>
                </template>
            </div>

            <div class="px-4 pt-3 border-t mt-3">
                <h2 class="text-[11px] font-semibold text-gray-500 mb-1">Actions</h2>
                <template x-for="block in actionBlocks" :key="block.type">
                    <div class="px-3 py-2 mb-2 text-xs bg-gray-50 border rounded cursor-move hover:bg-gray-100"
                         draggable="true"
                         @dragstart="onPaletteDragStart($event, block)">
                        <span x-text="block.label"></span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Main canvas + form -->
        <div class="flex-1 flex flex-col">
            <div class="flex items-center justify-between px-4 py-3 border-b bg-white">
                <div class="flex items-center gap-3">
                    <input type="text"
                           class="border-gray-300 rounded-md text-sm w-60"
                           placeholder="Automation name"
                           x-model="name">
                    <select class="border-gray-300 rounded-md text-sm"
                            x-model="trigger">
                        <option value="">Select trigger</option>
                        <option value="cod_order">COD order created</option>
                        <option value="abandoned_cart">Abandoned cart</option>
                        <option value="order_fulfilled">Order fulfilled</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button"
                            class="px-3 py-1.5 text-xs border rounded-md text-gray-600 hover:bg-gray-100"
                            @click="resetFlow()">
                        Reset
                    </button>

                    <form method="POST" action="{{ $isEdit ? route('automations.update', [$company, $automation]) : route('automations.store', $company) }}"
                          @submit.prevent="submitForm($event)">
                        @csrf
                        <input type="hidden" name="name" x-model="name">
                        <input type="hidden" name="trigger" x-model="trigger">
                        <input type="hidden" name="definition" x-ref="definition">
                        <button type="submit"
                                class="px-4 py-1.5 text-xs rounded-md bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
                            {{ $isEdit ? 'Update' : 'Save' }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="flex-1 flex overflow-hidden">
                <!-- Canvas -->
                <div class="flex-1 overflow-auto p-6">
                    <div class="flex flex-col items-center">
                        <!-- Trigger card -->
                        <div class="bg-white border rounded-lg shadow-sm p-4 w-80 mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-semibold">T</span>
                                <div>
                                    <div class="text-sm font-semibold">Trigger</div>
                                    <div class="text-[11px] text-gray-500">
                                        Start when <span x-text="triggerLabel() || '...select trigger'"></span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-[11px] text-gray-500">
                                Choose COD, abandoned cart or order fulfilled to start this automation.
                            </p>
                        </div>

                        <!-- Steps canvas -->
                        <div id="steps-canvas"
                             class="bg-white/80 border border-dashed border-gray-300 rounded-xl min-h-[320px] w-full max-w-3xl flex flex-col items-center py-6 px-4"
                             @dragover.prevent
                             @drop="onCanvasDrop($event)">
                            <template x-if="steps.length === 0">
                                <div class="text-[11px] text-gray-400 text-center">
                                    Drag blocks from the left to build your flow.
                                </div>
                            </template>

                            <div id="steps-list" class="space-y-4 w-full max-w-xl mt-2">
                                <template x-for="(step, idx) in steps" :key="step.id">
                                    <div class="relative" :data-id="step.id">
                                        <div class="absolute -left-4 top-1/2 -translate-y-1/2 w-4 h-px bg-gray-300"></div>

                                        <div class="bg-white border rounded-lg shadow-sm px-4 py-3 flex items-start justify-between cursor-move"
                                             @click="selectStep(step.id)"
                                             :class="selectedStepId === step.id ? 'ring-2 ring-indigo-500' : ''">
                                            <div class="flex items-start gap-3">
                                                <span class="mt-1 inline-flex items-center justify-center w-7 h-7 rounded-full text-[11px] font-semibold"
                                                      :class="step.type_badge_class"
                                                      x-text="step.short"></span>
                                                <div>
                                                    <div class="text-sm font-semibold" x-text="step.label"></div>
                                                    <div class="text-[11px] text-gray-500" x-text="step.description"></div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                        class="text-[11px] text-gray-400 hover:text-gray-600"
                                                        @click.stop="duplicateStep(idx)">⧉</button>
                                                <button type="button"
                                                        class="text-[11px] text-red-400 hover:text-red-600"
                                                        @click.stop="removeStep(idx)">✕</button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings panel -->
                <div class="w-80 border-l bg-white hidden lg:flex flex-col">
                    <div class="px-4 py-3 border-b">
                        <div class="text-sm font-semibold">Step settings</div>
                        <div class="text-[11px] text-gray-500">Configure message text, media, delay, etc.</div>
                    </div>
                    <div class="flex-1 overflow-auto p-4" x-show="selectedStep">
                        <template x-if="!selectedStep">
                            <p class="text-[11px] text-gray-400">Select a step on canvas.</p>
                        </template>

                        <template x-if="selectedStep">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-600 mb-1">Label</label>
                                    <input type="text"
                                           class="w-full border-gray-300 rounded-md text-xs"
                                           x-model="selectedStep.label"
                                           @input="syncSelectedStep()">
                                </div>

               <template x-if="selectedStep.type === 'simple_text'">
    <div>
        <label class="block text-[11px] font-medium text-gray-600 mb-1">Message text</label>
        <textarea rows="4"
                  class="w-full border-gray-300 rounded-md text-xs"
                  x-model="selectedStep.config.text"
                  @input="syncSelectedStep()"
                  placeholder="Hi @{{ customer_name }}, your COD order @{{ order_id }} of ₹@{{ order_total }} is received."></textarea>
        <p class="text-[10px] text-gray-400 mt-1">
            Variables: @{{ customer_name }}, @{{ order_total }}, @{{ order_id }}, @{{ cart_total }}.
        </p>
    </div>
</template>
<template x-if="selectedStep.type === 'media'">
    <div class="space-y-3">
        <div>
            <label class="block text-[11px] font-medium text-gray-600 mb-1">Media type</label>
            <select class="w-full border-gray-300 rounded-md text-xs"
                    x-model="selectedStep.config.media_type"
                    @change="syncSelectedStep()">
                <option value="image">Image</option>
                <option value="video">Video</option>
                <option value="document">Document</option>
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-medium text-gray-600 mb-1">Media URL</label>
            <input type="text"
                   class="w-full border-gray-300 rounded-md text-xs"
                   x-model="selectedStep.config.media_url"
                   @input="syncSelectedStep()"
                   placeholder="https://...">
        </div>
        <div>
            <label class="block text-[11px] font-medium text-gray-600 mb-1">Caption (optional)</label>
            <textarea rows="3"
                      class="w-full border-gray-300 rounded-md text-xs"
                      x-model="selectedStep.config.caption"
                      @input="syncSelectedStep()"
                      placeholder="Caption with variables like @{{ customer_name }}."></textarea>
        </div>
    </div>
</template>

<template x-if="selectedStep.type === 'buttons'">
    <div class="space-y-3">
        <div>
            <label class="block text-[11px] font-medium text-gray-600 mb-1">Template name</label>
            <input type="text"
                   class="w-full border-gray-300 rounded-md text-xs"
                   x-model="selectedStep.config.template_name"
                   @input="syncSelectedStep()"
                   placeholder="your_approved_template_name">
            <p class="text-[10px] text-gray-400 mt-1">
                Must match an approved WhatsApp template in your Whatify account. [attached_file:22][web:115]
            </p>
        </div>
        <div>
            <label class="block text-[11px] font-medium text-gray-600 mb-1">Language code</label>
            <input type="text"
                   class="w-full border-gray-300 rounded-md text-xs"
                   x-model="selectedStep.config.language_code"
                   @input="syncSelectedStep()"
                   placeholder="en">
        </div>
        <div>
            <label class="block text-[11px] font-medium text-gray-600 mb-1">Body text (for variable preview)</label>
            <textarea rows="3"
                      class="w-full border-gray-300 rounded-md text-xs"
                      x-model="selectedStep.config.body"
                      @input="syncSelectedStep()"
                      placeholder="Hi @{{ customer_name }}, your COD order @{{ order_id }} of ₹@{{ order_total }} is confirmed."></textarea>
        </div>

        <div>
            <label class="block text-[11px] font-medium text-gray-600 mb-1">Buttons</label>
            <template x-for="(btn, bIndex) in selectedStep.config.buttons" :key="bIndex">
                <div class="flex items-center gap-2 mb-2">
                    <input type="text"
                           class="flex-1 border-gray-300 rounded-md text-xs"
                           x-model="btn.label"
                           @input="syncSelectedStep()"
                           placeholder="Button label">
                    <input type="text"
                           class="flex-1 border-gray-300 rounded-md text-xs"
                           x-model="btn.payload"
                           @input="syncSelectedStep()"
                           placeholder="Payload eg. COD_CONFIRM">
                    <button type="button"
                            class="text-[11px] text-red-400 hover:text-red-600"
                            @click="selectedStep.config.buttons.splice(bIndex,1); syncSelectedStep();">
                        ✕
                    </button>
                </div>
            </template>
            <button type="button"
                    class="mt-1 px-2 py-1 text-[11px] border rounded-md text-gray-600 hover:bg-gray-50"
                    @click="selectedStep.config.buttons.push({label:'New button', payload:'PAYLOAD'}); syncSelectedStep();">
                + Add button
            </button>
        </div>
    </div>
</template>



                                <template x-if="selectedStep.type === 'delay'">
                                    <div class="flex gap-2">
                                        <div class="flex-1">
                                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Delay</label>
                                            <input type="number"
                                                   min="1"
                                                   class="w-full border-gray-300 rounded-md text-xs"
                                                   x-model.number="selectedStep.config.delay_value"
                                                   @input="syncSelectedStep()">
                                        </div>
                                        <div class="w-24">
                                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Unit</label>
                                            <select class="w-full border-gray-300 rounded-md text-xs"
                                                    x-model="selectedStep.config.delay_unit"
                                                    @change="syncSelectedStep()">
                                                <option value="minutes">Minutes</option>
                                                <option value="hours">Hours</option>
                                            </select>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function automationBuilder(initialName, initialTrigger, initialFlow) {
            return {
                name: initialName || '',
                trigger: initialTrigger || '',
                steps: (initialFlow && initialFlow.steps) ? initialFlow.steps : [],
                draggedBlock: null,
                selectedStepId: null,

                get selectedStep() {
                    return this.steps.find(s => s.id === this.selectedStepId) || null;
                },

messageBlocks: [
    { type: 'simple_text', label: 'Simple text', short: 'TXT', description: 'Send a plain WhatsApp message.' },
    { type: 'media',       label: 'Media files', short: 'MED', description: 'Send image, video or document.' },
    { type: 'buttons',     label: 'Interactive buttons', short: 'BTN', description: 'Send a template with quick-reply buttons.' },
],

actionBlocks: [
    { type: 'delay', label: 'Delay', short: 'WAIT', description: 'Pause before next step.' },
],

                init() {
                    // assign ids and badge class for existing steps
                    this.steps = (this.steps || []).map(s => {
                        if (!s.id) s.id = Date.now().toString() + '-' + Math.random().toString(36).slice(2,7);
                        s.type_badge_class = this.badgeClass(s.type);
                        s.short = s.short || this.shortLabel(s.type);
                        return s;
                    });
                    const el = document.getElementById('steps-list');
                    if (el) {
                        new Sortable(el, {
                            animation: 150,
                            handle: '.cursor-move',
                            onEnd: (evt) => {
                                const moved = this.steps.splice(evt.oldIndex, 1)[0];
                                this.steps.splice(evt.newIndex, 0, moved);
                            }
                        });
                    }
                },

                triggerLabel() {
                    if (this.trigger === 'cod_order') return 'a COD order is created';
                    if (this.trigger === 'abandoned_cart') return 'a cart is abandoned';
                    if (this.trigger === 'order_fulfilled') return 'an order is fulfilled';
                    return '';
                },

                onPaletteDragStart(e, block) {
                    this.draggedBlock = block;
                    e.dataTransfer.effectAllowed = 'copyMove';
                },

                onCanvasDrop() {
                    if (!this.draggedBlock) return;
                    this.addStepFromBlock(this.draggedBlock);
                    this.draggedBlock = null;
                },

addStepFromBlock(block) {
    const id = Date.now().toString() + '-' + Math.random().toString(36).slice(2,7);
    const base = {
        id,
        type: block.type,
        label: block.label,
        short: block.short,
        description: block.description,
        type_badge_class: this.badgeClass(block.type),
        config: {}
    };

    if (block.type === 'simple_text') {
        base.config = {
            text: 'Hi @{{ customer_name }}, your order @{{ order_id }} of ₹@{{ order_total }} is confirmed.'
        };
    } else if (block.type === 'media') {
        base.config = {
            media_type: 'image',
            media_url: '',
            caption: ''
        };
    } else if (block.type === 'buttons') {
        base.config = {
            template_name: '',
            language_code: 'en',
            body: 'Hi @{{ customer_name }}, your COD order @{{ order_id }} of ₹@{{ order_total }} is confirmed.',
            buttons: [
                { label: 'Yes, confirm', payload: 'COD_CONFIRM' },
                { label: 'Cancel order', payload: 'COD_CANCEL' }
            ]
        };
    } else if (block.type === 'delay') {
        base.config = { delay_value: 15, delay_unit: 'minutes' };
    }

    this.steps.push(base);
    this.selectedStepId = id;
},
badgeClass(type) {
    if (type === 'simple_text') return 'bg-blue-100 text-blue-700';
    if (type === 'media')       return 'bg-purple-100 text-purple-700';
    if (type === 'buttons')     return 'bg-emerald-100 text-emerald-700';
    if (type === 'delay')       return 'bg-amber-100 text-amber-700';
    return 'bg-gray-100 text-gray-700';
},

shortLabel(type) {
    if (type === 'simple_text') return 'TXT';
    if (type === 'media')       return 'MED';
    if (type === 'buttons')     return 'BTN';
    if (type === 'delay')       return 'WAIT';
    return 'STEP';
},
                selectStep(id) {
                    this.selectedStepId = id;
                },

                duplicateStep(index) {
                    const original = this.steps[index];
                    const copy = JSON.parse(JSON.stringify(original));
                    copy.id = Date.now().toString() + '-' + Math.random().toString(36).slice(2,7);
                    this.steps.splice(index + 1, 0, copy);
                },

                removeStep(index) {
                    if (this.steps[index]?.id === this.selectedStepId) {
                        this.selectedStepId = null;
                    }
                    this.steps.splice(index, 1);
                },

                syncSelectedStep() {
                    if (!this.selectedStep) return;
                    const idx = this.steps.findIndex(s => s.id === this.selectedStep.id);
                    if (idx !== -1) {
                        this.steps.splice(idx, 1, JSON.parse(JSON.stringify(this.selectedStep)));
                    }
                },

                resetFlow() {
                    this.steps = [];
                    this.selectedStepId = null;
                },

                submitForm(e) {
                    if (!this.name || !this.trigger) {
                        alert('Enter name and select trigger.');
                        return;
                    }
                    if (this.steps.length === 0) {
                        alert('Add at least one step.');
                        return;
                    }
                    const payload = {
                        trigger: this.trigger,
                        steps: this.steps
                    };
                    this.$refs.definition.value = JSON.stringify(payload);
                    e.target.submit();
                }
            }
        }
    </script>
</x-layouts.app>
